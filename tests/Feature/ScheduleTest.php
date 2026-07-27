<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Equipment;
use App\Models\ScheduledVisit;
use App\Models\Site;
use App\Models\TechnicianSchedule;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Database\Seeders\ScheduleSettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Fase 1 del cronograma: jornada, descanso, solapes y cascada de duracion.
 *
 * Las fechas son de una semana concreta a proposito (2026-08-03 es lunes) para que
 * "cae en sabado" o "es dia laborable" no dependan del dia en que se corra la suite.
 */
class ScheduleTest extends TestCase
{
    use RefreshDatabase;

    private User $coordinator;

    private User $technician;

    private Equipment $equipment;

    private const MONDAY = '2026-08-03';

    private const SATURDAY = '2026-08-08';

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([PermissionSeeder::class, RoleSeeder::class, ScheduleSettingSeeder::class]);

        $this->coordinator = User::factory()->create();
        $this->coordinator->assignRole('coordinator');

        $this->technician = User::factory()->create(['name' => 'Técnico Uno']);
        $this->technician->assignRole('technician');

        $client = Client::create([
            'business_name' => 'Edificio Central',
            'nit' => '900123456-1',
        ]);

        $site = Site::create([
            'client_id' => $client->id,
            'name' => 'Torre A',
            'address' => 'Calle 1 # 2-3',
        ]);

        $this->equipment = Equipment::create([
            'site_id' => $site->id,
            'internal_code' => 'TZ-TEST-0001',
            'equipment_type' => 'ascensor',
        ]);
    }

    private function schedule(string $date, string $start, string $end, array $extra = []): \Illuminate\Testing\TestResponse
    {
        Sanctum::actingAs($this->coordinator);

        return $this->postJson('/api/schedule/visits', array_merge([
            'equipment_id' => $this->equipment->id,
            'technician_id' => $this->technician->id,
            'scheduled_start' => "$date $start",
            'scheduled_end' => "$date $end",
        ], $extra));
    }

    public function test_programa_una_visita_dentro_de_la_jornada(): void
    {
        $this->schedule(self::MONDAY, '09:00', '10:30')->assertCreated();

        $this->assertDatabaseHas('scheduled_visits', [
            'equipment_id' => $this->equipment->id,
            'technician_id' => $this->technician->id,
            'status' => 'programada',
        ]);
    }

    /** site_id y client_id se derivan del equipo, no de lo que mande el cliente. */
    public function test_desnormaliza_sede_y_cliente_desde_el_equipo(): void
    {
        $this->schedule(self::MONDAY, '09:00', '10:30')->assertCreated();

        $visit = ScheduledVisit::first();

        $this->assertSame($this->equipment->site_id, $visit->site_id);
        $this->assertSame($this->equipment->site->client_id, $visit->client_id);
        $this->assertNotNull($visit->uuid);
    }

    public function test_rechaza_una_visita_antes_de_que_abra_la_jornada(): void
    {
        $this->schedule(self::MONDAY, '07:00', '08:30')
            ->assertStatus(422)
            ->assertJsonValidationErrors('scheduled_start');
    }

    public function test_rechaza_una_visita_que_termina_despues_del_cierre(): void
    {
        $this->schedule(self::MONDAY, '17:00', '18:30')->assertStatus(422);
    }

    public function test_rechaza_una_visita_que_pisa_el_descanso(): void
    {
        $response = $this->schedule(self::MONDAY, '12:30', '14:00')->assertStatus(422);

        $this->assertStringContainsString(
            'descanso',
            implode(' ', $response->json('errors.scheduled_start')),
        );
    }

    public function test_rechaza_una_visita_en_dia_no_laborable(): void
    {
        $response = $this->schedule(self::SATURDAY, '09:00', '10:30')->assertStatus(422);

        $this->assertStringContainsString(
            'no es un dia laborable',
            implode(' ', $response->json('errors.scheduled_start')),
        );
    }

    public function test_acepta_una_visita_pegada_al_final_del_descanso(): void
    {
        $this->schedule(self::MONDAY, '14:00', '15:30')->assertCreated();
    }

    public function test_rechaza_dos_visitas_solapadas_del_mismo_tecnico(): void
    {
        $this->schedule(self::MONDAY, '09:00', '10:30')->assertCreated();

        $response = $this->schedule(self::MONDAY, '10:00', '11:00')->assertStatus(422);

        $this->assertStringContainsString(
            'Se cruza',
            implode(' ', $response->json('errors.scheduled_start')),
        );
    }

    /** Empezar justo donde acaba la anterior no es solape. */
    public function test_acepta_visitas_consecutivas_sin_hueco(): void
    {
        $this->schedule(self::MONDAY, '09:00', '10:30')->assertCreated();
        $this->schedule(self::MONDAY, '10:30', '12:00')->assertCreated();
    }

    /** El solape es por tecnico: dos tecnicos pueden coincidir en horario. */
    public function test_permite_el_mismo_horario_a_dos_tecnicos_distintos(): void
    {
        $otro = User::factory()->create();
        $otro->assignRole('technician');

        $this->schedule(self::MONDAY, '09:00', '10:30')->assertCreated();
        $this->schedule(self::MONDAY, '09:00', '10:30', ['technician_id' => $otro->id])->assertCreated();
    }

    public function test_una_jornada_propia_habilita_el_sabado(): void
    {
        $this->schedule(self::SATURDAY, '09:00', '10:30')->assertStatus(422);

        TechnicianSchedule::create([
            'user_id' => $this->technician->id,
            'enabled' => true,
            'working_days' => [1, 2, 3, 4, 5, 6],
            'working_hours' => ['start' => '08:00', 'end' => '18:00'],
        ]);

        $this->schedule(self::SATURDAY, '09:00', '10:30')->assertCreated();
    }

    /** Una fila sin descanso significa "sin descanso", no "hereda el global". */
    public function test_una_jornada_propia_sin_descanso_libera_el_mediodia(): void
    {
        TechnicianSchedule::create([
            'user_id' => $this->technician->id,
            'enabled' => true,
            'break_start' => null,
            'break_end' => null,
        ]);

        $this->schedule(self::MONDAY, '13:15', '13:45')->assertCreated();
    }

    public function test_la_duracion_sale_del_equipo_si_lo_tiene_configurado(): void
    {
        Sanctum::actingAs($this->coordinator);

        $this->getJson("/api/schedule/equipment/{$this->equipment->id}/duration")
            ->assertOk()
            ->assertJson(['duration_minutes' => 90, 'is_custom' => false]);

        $this->equipment->update(['default_visit_duration_minutes' => 120]);

        $this->getJson("/api/schedule/equipment/{$this->equipment->id}/duration")
            ->assertOk()
            ->assertJson(['duration_minutes' => 120, 'is_custom' => true]);
    }

    public function test_mover_una_visita_al_descanso_se_rechaza(): void
    {
        $this->schedule(self::MONDAY, '09:00', '10:30')->assertCreated();
        $visit = ScheduledVisit::first();

        Sanctum::actingAs($this->coordinator);

        $this->putJson("/api/schedule/visits/{$visit->id}", [
            'scheduled_start' => self::MONDAY.' 13:00',
            'scheduled_end' => self::MONDAY.' 14:30',
        ])->assertStatus(422);

        // La visita no se movio
        $this->assertSame('09:00', $visit->fresh()->scheduled_start->format('H:i'));
    }

    public function test_mover_una_visita_a_un_hueco_libre_persiste(): void
    {
        $this->schedule(self::MONDAY, '09:00', '10:30')->assertCreated();
        $visit = ScheduledVisit::first();

        Sanctum::actingAs($this->coordinator);

        $this->putJson("/api/schedule/visits/{$visit->id}", [
            'scheduled_start' => self::MONDAY.' 15:45',
            'scheduled_end' => self::MONDAY.' 17:15',
        ])->assertOk();

        $this->assertSame('15:45', $visit->fresh()->scheduled_start->format('H:i'));
    }

    public function test_cancelar_deja_la_visita_fuera_del_calculo_de_solapes(): void
    {
        $this->schedule(self::MONDAY, '09:00', '10:30')->assertCreated();
        $visit = ScheduledVisit::first();

        Sanctum::actingAs($this->coordinator);
        $this->postJson("/api/schedule/visits/{$visit->id}/cancel", ['cancel_reason' => 'El cliente aplazó'])
            ->assertOk();

        $this->assertSame('cancelada', $visit->fresh()->status);

        // El hueco vuelve a estar libre
        $this->schedule(self::MONDAY, '09:00', '10:30')->assertCreated();
    }

    public function test_un_tecnico_no_entra_al_cronograma_de_coordinacion(): void
    {
        Sanctum::actingAs($this->technician);

        $this->getJson('/api/schedule/visits?from='.self::MONDAY.'&to='.self::SATURDAY)
            ->assertForbidden();
    }

    public function test_el_indice_exige_rango_de_fechas(): void
    {
        Sanctum::actingAs($this->coordinator);

        $this->getJson('/api/schedule/visits')
            ->assertStatus(422)
            ->assertJsonValidationErrors(['from', 'to']);
    }

    public function test_el_indice_filtra_por_tecnico(): void
    {
        $otro = User::factory()->create();
        $otro->assignRole('technician');

        $this->schedule(self::MONDAY, '09:00', '10:30')->assertCreated();
        $this->schedule(self::MONDAY, '09:00', '10:30', ['technician_id' => $otro->id])->assertCreated();

        Sanctum::actingAs($this->coordinator);

        $this->getJson('/api/schedule/visits?from='.self::MONDAY.'&to='.self::SATURDAY.'&technician_id='.$otro->id)
            ->assertOk()
            ->assertJsonCount(1);
    }
}
