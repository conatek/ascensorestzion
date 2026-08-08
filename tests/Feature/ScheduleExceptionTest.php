<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Equipment;
use App\Models\ScheduledVisit;
use App\Models\ScheduleException;
use App\Models\Site;
use App\Models\TechnicianSchedule;
use App\Models\User;
use App\Services\AvailabilityService;
use App\Services\ScheduleService;
use Carbon\CarbonImmutable;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Database\Seeders\ScheduleSettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Fase 4b: excepciones de jornada por fecha.
 *
 * Sin tecnico son festivos y aplican a todos; con tecnico son vacaciones o un
 * turno suelto, y ganan sobre la general.
 */
class ScheduleExceptionTest extends TestCase
{
    use RefreshDatabase;

    private User $coordinator;

    private User $technician;

    private Equipment $equipment;

    private ScheduleService $schedule;

    private AvailabilityService $availability;

    private const NOW = '2026-08-03 10:00:00';

    /** Lunes de la semana siguiente. */
    private const MONDAY = '2026-08-10';

    private const SATURDAY = '2026-08-15';

    protected function setUp(): void
    {
        parent::setUp();

        CarbonImmutable::setTestNow(self::NOW);
        $this->travelTo(self::NOW);

        $this->seed([PermissionSeeder::class, RoleSeeder::class, ScheduleSettingSeeder::class]);

        $this->coordinator = User::factory()->create();
        $this->coordinator->assignRole('coordinator');

        $this->technician = User::factory()->create(['name' => 'Técnico Uno']);
        $this->technician->assignRole('technician');

        $client = Client::create(['business_name' => 'Edificio Central', 'nit' => '900123456-1']);
        $site = Site::create(['client_id' => $client->id, 'name' => 'Torre A', 'address' => 'Calle 1']);

        $this->equipment = Equipment::create([
            'site_id' => $site->id,
            'internal_code' => 'TZ-A-0001',
            'equipment_type' => 'ascensor',
        ]);

        $this->schedule = app(ScheduleService::class);
        $this->availability = app(AvailabilityService::class);
    }

    protected function tearDown(): void
    {
        CarbonImmutable::setTestNow();

        parent::tearDown();
    }

    private function closeDay(string $date, ?int $userId = null, ?string $note = null): ScheduleException
    {
        return ScheduleException::create([
            'user_id' => $userId,
            'date' => $date,
            'working_hours' => null,
            'note' => $note,
        ]);
    }

    private function scheduleVisit(string $date, string $start = '09:00', string $end = '10:30')
    {
        Sanctum::actingAs($this->coordinator);

        return $this->postJson('/api/schedule/visits', [
            'equipment_id' => $this->equipment->id,
            'technician_id' => $this->technician->id,
            'scheduled_start' => "$date $start",
            'scheduled_end' => "$date $end",
        ]);
    }

    private function dayFor(string $date): array
    {
        $day = CarbonImmutable::parse($date);
        $visit = ScheduledVisit::create([
            'equipment_id' => $this->equipment->id,
            'site_id' => $this->equipment->site_id,
            'client_id' => $this->equipment->site->client_id,
            'technician_id' => $this->technician->id,
            'scheduled_start' => '2026-09-28 09:00',
            'scheduled_end' => '2026-09-28 10:30',
            'status' => 'programada',
            'created_by' => $this->coordinator->id,
        ]);

        return $this->availability->forVisit($visit, $day, $day)['days'][0];
    }

    // ── La jornada del día ──

    public function test_un_festivo_general_deja_el_dia_sin_jornada(): void
    {
        $this->closeDay(self::MONDAY, null, 'Festivo');

        $window = $this->schedule->workingWindowFor($this->technician, CarbonImmutable::parse(self::MONDAY));

        $this->assertSame([], $window['days']);
        $this->assertSame('cerrado', $window['exception']['type']);
        $this->assertSame('Festivo', $window['exception']['note']);
    }

    public function test_sin_fecha_la_jornada_sigue_siendo_la_habitual(): void
    {
        $this->closeDay(self::MONDAY, null, 'Festivo');

        // El grid del calendario y la ficha del técnico piden la jornada de
        // siempre: no tendría sentido que un festivo se la vaciara.
        $window = $this->schedule->workingWindowFor($this->technician);

        $this->assertSame([1, 2, 3, 4, 5], $window['days']);
        $this->assertNull($window['exception']);
    }

    public function test_la_excepcion_del_tecnico_gana_sobre_la_general(): void
    {
        $this->closeDay(self::MONDAY, null, 'Festivo');
        ScheduleException::create([
            'user_id' => $this->technician->id,
            'date' => self::MONDAY,
            'working_hours' => ['start' => '08:00', 'end' => '12:00'],
            'note' => 'Guardia',
        ]);

        $window = $this->schedule->workingWindowFor($this->technician, CarbonImmutable::parse(self::MONDAY));

        $this->assertSame('personalizada', $window['exception']['type']);
        $this->assertSame('08:00', $window['start']);
        $this->assertSame('12:00', $window['end']);
    }

    public function test_un_horario_especial_habilita_un_dia_no_laborable(): void
    {
        ScheduleException::create([
            'user_id' => $this->technician->id,
            'date' => self::SATURDAY,
            'working_hours' => ['start' => '08:00', 'end' => '12:00'],
            'note' => 'Sábado puntual',
        ]);

        $window = $this->schedule->workingWindowFor($this->technician, CarbonImmutable::parse(self::SATURDAY));

        $this->assertContains(6, $window['days']);
    }

    /** Una excepción de otro técnico no puede afectar a este. */
    public function test_la_excepcion_de_otro_tecnico_no_aplica(): void
    {
        $otro = User::factory()->create();
        $otro->assignRole('technician');

        $this->closeDay(self::MONDAY, $otro->id, 'Vacaciones de otro');

        $window = $this->schedule->workingWindowFor($this->technician, CarbonImmutable::parse(self::MONDAY));

        $this->assertNull($window['exception']);
        $this->assertSame([1, 2, 3, 4, 5], $window['days']);
    }

    public function test_la_excepcion_se_superpone_a_la_jornada_propia_del_tecnico(): void
    {
        TechnicianSchedule::create([
            'user_id' => $this->technician->id,
            'enabled' => true,
            'working_days' => [1, 2, 3, 4, 5, 6],
            'working_hours' => ['start' => '07:00', 'end' => '16:00'],
            'break_start' => null,
            'break_end' => null,
        ]);

        $this->closeDay(self::SATURDAY, $this->technician->id, 'Vacaciones');

        $window = $this->schedule->workingWindowFor($this->technician, CarbonImmutable::parse(self::SATURDAY));

        $this->assertSame([], $window['days']);
    }

    // ── Validación al programar ──

    public function test_no_se_puede_programar_en_un_festivo(): void
    {
        $this->closeDay(self::MONDAY, null, 'Festivo');

        $response = $this->scheduleVisit(self::MONDAY)->assertStatus(422);

        $this->assertStringContainsString(
            'no se trabaja (Festivo)',
            implode(' ', $response->json('errors.scheduled_start')),
        );
    }

    public function test_no_se_puede_programar_en_las_vacaciones_del_tecnico(): void
    {
        $this->closeDay(self::MONDAY, $this->technician->id, 'Vacaciones');

        $this->scheduleVisit(self::MONDAY)->assertStatus(422);
    }

    public function test_si_se_puede_programar_un_sabado_habilitado_por_excepcion(): void
    {
        ScheduleException::create([
            'user_id' => $this->technician->id,
            'date' => self::SATURDAY,
            'working_hours' => ['start' => '08:00', 'end' => '12:00'],
        ]);

        $this->scheduleVisit(self::SATURDAY, '08:00', '09:30')->assertCreated();
    }

    public function test_el_horario_especial_acota_la_jornada_de_ese_dia(): void
    {
        ScheduleException::create([
            'user_id' => $this->technician->id,
            'date' => self::MONDAY,
            'working_hours' => ['start' => '08:00', 'end' => '12:00'],
        ]);

        // 14:00 cae dentro de la jornada normal pero fuera de la del día.
        $this->scheduleVisit(self::MONDAY, '14:00', '15:30')->assertStatus(422);
        $this->scheduleVisit(self::MONDAY, '08:00', '09:30')->assertCreated();
    }

    public function test_un_dia_normal_sigue_aceptando_visitas(): void
    {
        $this->closeDay(self::MONDAY, null, 'Festivo');

        // El martes no tiene excepción: nada cambia.
        $this->scheduleVisit('2026-08-11')->assertCreated();
    }

    // ── Disponibilidad ──

    public function test_la_disponibilidad_no_ofrece_huecos_en_un_festivo(): void
    {
        $this->closeDay(self::MONDAY, null, 'Festivo de prueba');

        $day = $this->dayFor(self::MONDAY);

        $this->assertFalse($day['is_working_day']);
        $this->assertSame(0, $day['slot_count']);
        $this->assertSame('excepcion', $day['reason']);
        $this->assertSame('Festivo de prueba', $day['exception_note']);
    }

    public function test_la_disponibilidad_distingue_un_festivo_de_un_domingo(): void
    {
        // 2026-08-16 es domingo, sin excepción ninguna.
        $day = $this->dayFor('2026-08-16');

        $this->assertSame('no_laborable', $day['reason']);
        $this->assertNull($day['exception_note']);
    }

    public function test_la_disponibilidad_ofrece_el_sabado_habilitado_por_excepcion(): void
    {
        ScheduleException::create([
            'user_id' => $this->technician->id,
            'date' => self::SATURDAY,
            'working_hours' => ['start' => '08:00', 'end' => '12:00'],
        ]);

        $day = $this->dayFor(self::SATURDAY);

        $this->assertTrue($day['is_working_day']);
        $this->assertGreaterThan(0, $day['slot_count']);
    }

    /** La invariante de la fase 4 tiene que seguir en pie con excepciones de por medio. */
    public function test_todo_espacio_ofrecido_sigue_pasando_la_validacion(): void
    {
        $this->closeDay('2026-08-11', null, 'Festivo');
        ScheduleException::create([
            'user_id' => $this->technician->id,
            'date' => self::SATURDAY,
            'working_hours' => ['start' => '08:00', 'end' => '12:00'],
        ]);

        $visit = ScheduledVisit::create([
            'equipment_id' => $this->equipment->id,
            'site_id' => $this->equipment->site_id,
            'client_id' => $this->equipment->site->client_id,
            'technician_id' => $this->technician->id,
            'scheduled_start' => '2026-09-28 09:00',
            'scheduled_end' => '2026-09-28 10:30',
            'status' => 'programada',
            'created_by' => $this->coordinator->id,
        ]);

        $from = CarbonImmutable::parse(self::MONDAY);
        $result = $this->availability->forVisit($visit, $from, $from->addDays(6));

        $checked = 0;

        foreach ($result['slots'] as $daySlots) {
            foreach ($daySlots as $slot) {
                $this->schedule->assertSlotIsFree(
                    $this->technician,
                    CarbonImmutable::parse($slot['value']),
                    CarbonImmutable::parse($slot['end_value']),
                    $visit->id,
                );
                $checked++;
            }
        }

        $this->assertGreaterThan(20, $checked);
        // Y el festivo no aportó ninguno.
        $this->assertArrayNotHasKey('2026-08-11', $result['slots']);
    }

    // ── API ──

    public function test_coordinacion_crea_un_festivo_general(): void
    {
        Sanctum::actingAs($this->coordinator);

        $this->postJson('/api/schedule/exceptions', [
            'date' => '2026-12-25',
            'note' => 'Navidad',
        ])->assertCreated()->assertJsonPath('exceptions.0.user_id', null);

        $this->assertDatabaseHas('schedule_exceptions', ['date' => '2026-12-25', 'user_id' => null]);
    }

    public function test_un_rango_crea_una_fila_por_dia(): void
    {
        Sanctum::actingAs($this->coordinator);

        $this->postJson('/api/schedule/exceptions', [
            'user_id' => $this->technician->id,
            'date' => '2026-09-14',
            'date_end' => '2026-09-18',
            'note' => 'Vacaciones',
        ])->assertCreated();

        $this->assertSame(5, ScheduleException::where('user_id', $this->technician->id)->count());
    }

    public function test_guardar_dos_veces_el_mismo_dia_lo_corrige_en_vez_de_duplicar(): void
    {
        Sanctum::actingAs($this->coordinator);

        $payload = ['date' => '2026-12-25', 'note' => 'Navidad'];
        $this->postJson('/api/schedule/exceptions', $payload)->assertCreated();
        $this->postJson('/api/schedule/exceptions', ['date' => '2026-12-25', 'note' => 'Navidad (corregido)'])
            ->assertCreated();

        $this->assertSame(1, ScheduleException::whereNull('user_id')->count());
        $this->assertSame('Navidad (corregido)', ScheduleException::first()->note);
    }

    public function test_cerrar_un_dia_avisa_de_las_visitas_que_ya_habia(): void
    {
        $this->scheduleVisit(self::MONDAY)->assertCreated();

        Sanctum::actingAs($this->coordinator);

        $response = $this->postJson('/api/schedule/exceptions', [
            'date' => self::MONDAY,
            'note' => 'Festivo',
        ])->assertCreated();

        // No bloquea, pero lo dice: coordinación tiene que enterarse ahora.
        $this->assertCount(1, $response->json('affected_visits'));
        $this->assertSame('TZ-A-0001', $response->json('affected_visits.0.equipment_code'));
    }

    public function test_un_rango_desmedido_se_rechaza(): void
    {
        Sanctum::actingAs($this->coordinator);

        $this->postJson('/api/schedule/exceptions', [
            'date' => '2026-01-01',
            'date_end' => '2026-12-31',
        ])->assertStatus(422);
    }

    public function test_el_indice_devuelve_las_del_tecnico_y_las_generales(): void
    {
        $this->closeDay('2026-12-25', null, 'Navidad');
        $this->closeDay('2026-09-14', $this->technician->id, 'Vacaciones');
        $otro = User::factory()->create();
        $this->closeDay('2026-09-20', $otro->id, 'Vacaciones de otro');

        Sanctum::actingAs($this->coordinator);

        $notes = collect(
            $this->getJson("/api/schedule/exceptions?user_id={$this->technician->id}")
                ->assertOk()->json('exceptions')
        )->pluck('note');

        $this->assertContains('Navidad', $notes);
        $this->assertContains('Vacaciones', $notes);
        $this->assertNotContains('Vacaciones de otro', $notes);
    }

    public function test_se_borra_una_excepcion(): void
    {
        $exception = $this->closeDay('2026-12-25', null, 'Navidad');

        Sanctum::actingAs($this->coordinator);

        $this->deleteJson("/api/schedule/exceptions/{$exception->id}")->assertOk();

        $this->assertSame(0, ScheduleException::count());
    }

    public function test_un_tecnico_no_puede_crear_excepciones(): void
    {
        Sanctum::actingAs($this->technician);

        $this->postJson('/api/schedule/exceptions', ['date' => '2026-12-25'])->assertForbidden();
    }

    public function test_una_excepcion_con_horario_invertido_se_rechaza(): void
    {
        Sanctum::actingAs($this->coordinator);

        $this->postJson('/api/schedule/exceptions', [
            'date' => '2026-12-25',
            'working_hours' => ['start' => '14:00', 'end' => '09:00'],
        ])->assertStatus(422)->assertJsonValidationErrors('working_hours.end');
    }

    public function test_borrar_al_tecnico_se_lleva_sus_excepciones(): void
    {
        $this->closeDay('2026-09-14', $this->technician->id, 'Vacaciones');
        $this->closeDay('2026-12-25', null, 'Navidad');

        $this->technician->delete();

        // La suya cae con él; el festivo general se queda.
        $this->assertSame(1, ScheduleException::count());
        $this->assertNull(ScheduleException::first()->user_id);
    }

    public function test_una_excepcion_no_toca_las_visitas_ya_programadas(): void
    {
        $this->scheduleVisit(self::MONDAY)->assertCreated();
        $visit = ScheduledVisit::first();

        $this->closeDay(self::MONDAY, null, 'Festivo');

        // Se avisa, pero no se cancela ni se mueve nada por la espalda.
        $this->assertSame('programada', $visit->refresh()->status);
        $this->assertSame(self::MONDAY.' 09:00', $visit->scheduled_start->format('Y-m-d H:i'));
    }

    /** @throws ValidationException */
    public function test_una_visita_ya_programada_en_un_dia_que_luego_se_cierra_no_se_puede_mover_ahi(): void
    {
        $this->closeDay(self::MONDAY, null, 'Festivo');

        $this->expectException(ValidationException::class);

        $this->schedule->assertSlotIsFree(
            $this->technician,
            CarbonImmutable::parse(self::MONDAY.' 09:00'),
            CarbonImmutable::parse(self::MONDAY.' 10:30'),
        );
    }
}
