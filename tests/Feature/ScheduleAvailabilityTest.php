<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Equipment;
use App\Models\ScheduledVisit;
use App\Models\ScheduleSetting;
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
use Tests\TestCase;

/**
 * Fase 4: el calculo de espacios libres que alimenta los chips del portal.
 *
 * Semana fija (2026-08-03 es lunes) y tiempo congelado: toda la fase gira
 * alrededor de now() + la antelacion minima.
 */
class ScheduleAvailabilityTest extends TestCase
{
    use RefreshDatabase;

    private User $technician;

    private Equipment $equipment;

    private AvailabilityService $availability;

    private ScheduleService $schedule;

    /** Un lunes a las 10 de la mañana. */
    private const NOW = '2026-08-03 10:00:00';

    /** Lunes de la semana siguiente: fuera de la antelacion minima. */
    private const MONDAY = '2026-08-10';

    private const SATURDAY = '2026-08-15';

    protected function setUp(): void
    {
        parent::setUp();

        CarbonImmutable::setTestNow(self::NOW);
        $this->travelTo(self::NOW);

        $this->seed([PermissionSeeder::class, RoleSeeder::class, ScheduleSettingSeeder::class]);

        $this->technician = User::factory()->create(['name' => 'Técnico Uno']);
        $this->technician->assignRole('technician');

        $client = Client::create(['business_name' => 'Edificio Central', 'nit' => '900123456-1']);
        $site = Site::create(['client_id' => $client->id, 'name' => 'Torre A', 'address' => 'Calle 1']);

        $this->equipment = Equipment::create([
            'site_id' => $site->id,
            'internal_code' => 'TZ-A-0001',
            'equipment_type' => 'ascensor',
        ]);

        $this->availability = app(AvailabilityService::class);
        $this->schedule = app(ScheduleService::class);
    }

    protected function tearDown(): void
    {
        CarbonImmutable::setTestNow();

        parent::tearDown();
    }

    private function visit(string $date, string $start, string $end, array $extra = []): ScheduledVisit
    {
        return ScheduledVisit::create(array_merge([
            'equipment_id' => $this->equipment->id,
            'site_id' => $this->equipment->site_id,
            'client_id' => $this->equipment->site->client_id,
            'technician_id' => $this->technician->id,
            'scheduled_start' => "$date $start",
            'scheduled_end' => "$date $end",
            'status' => 'programada',
            'created_by' => $this->technician->id,
        ], $extra));
    }

    /** @return string[] los labels de un dia concreto */
    private function labelsFor(ScheduledVisit $visit, string $date): array
    {
        $day = CarbonImmutable::parse($date);
        $result = $this->availability->forVisit($visit, $day, $day);

        return array_column($result['slots'][$date] ?? [], 'label');
    }

    private function dayFor(ScheduledVisit $visit, string $date): array
    {
        $day = CarbonImmutable::parse($date);
        $result = $this->availability->forVisit($visit, $day, $day);

        return $result['days'][0];
    }

    public function test_la_disponibilidad_no_ofrece_horarios_fuera_de_la_jornada(): void
    {
        $visit = $this->visit(self::MONDAY, '09:00', '10:30');

        $labels = $this->labelsFor($visit, self::MONDAY);

        $this->assertSame('08:00', $labels[0]);
        // Jornada hasta las 18:00 y visita de 90 min: el ultimo arranque es 16:30.
        $this->assertSame('16:30', end($labels));
        $this->assertNotContains('07:30', $labels);
        $this->assertNotContains('17:00', $labels);
    }

    public function test_la_disponibilidad_no_ofrece_el_descanso_de_mediodia(): void
    {
        $visit = $this->visit(self::MONDAY, '09:00', '10:30');

        $labels = $this->labelsFor($visit, self::MONDAY);

        // 12:00-13:30 y 13:00-14:30 pisan el descanso; 11:30-13:00 y 14:00-15:30 no.
        $this->assertNotContains('12:00', $labels);
        $this->assertNotContains('12:30', $labels);
        $this->assertNotContains('13:00', $labels);
        $this->assertNotContains('13:30', $labels);
        $this->assertContains('11:30', $labels);
        $this->assertContains('14:00', $labels);
    }

    public function test_la_disponibilidad_no_ofrece_los_dias_no_laborables(): void
    {
        $visit = $this->visit(self::MONDAY, '09:00', '10:30');

        $day = $this->dayFor($visit, self::SATURDAY);

        $this->assertFalse($day['is_working_day']);
        $this->assertSame(0, $day['slot_count']);
        $this->assertSame('no_laborable', $day['reason']);
    }

    public function test_la_disponibilidad_respeta_la_jornada_propia_del_tecnico(): void
    {
        TechnicianSchedule::create([
            'user_id' => $this->technician->id,
            'enabled' => true,
            'working_days' => [1, 2, 3, 4, 5, 6],
            'working_hours' => ['start' => '07:00', 'end' => '12:00'],
            'break_start' => null,
            'break_end' => null,
        ]);

        $visit = $this->visit(self::MONDAY, '09:00', '10:30');

        $labels = $this->labelsFor($visit, self::SATURDAY);

        $this->assertSame(['07:00', '07:30', '08:00', '08:30', '09:00', '09:30', '10:00', '10:30'], $labels);
    }

    public function test_la_disponibilidad_descuenta_el_buffer_de_viaje_alrededor_de_las_visitas(): void
    {
        $moving = $this->visit(self::MONDAY, '09:00', '10:30');
        // Otra visita del mismo tecnico: bloquea 10:30-12:30 con el colchon de 30 min.
        $this->visit(self::MONDAY, '11:00', '12:00');

        $labels = $this->labelsFor($moving, self::MONDAY);

        $this->assertNotContains('10:30', $labels);
        $this->assertNotContains('11:00', $labels);
        // 08:00-09:30 termina antes de las 10:30, asi que sigue en pie.
        $this->assertContains('08:00', $labels);
    }

    public function test_la_disponibilidad_ignora_la_propia_visita_que_se_esta_moviendo(): void
    {
        // reprogramacion_solicitada es un estado bloqueante: sin ignoreVisitId la
        // visita se taparia a si misma y no habria forma de moverla dentro del dia.
        $visit = $this->visit(self::MONDAY, '09:00', '10:30', ['status' => 'reprogramacion_solicitada']);

        $labels = $this->labelsFor($visit, self::MONDAY);

        $this->assertContains('09:00', $labels);
        $this->assertContains('08:30', $labels);
    }

    public function test_la_disponibilidad_excluye_lo_que_cae_dentro_de_la_antelacion_minima(): void
    {
        // Mañana martes: dentro de las 24 h desde el lunes a las 10:00 solo a
        // partir de las 10:00, asi que la mañana temprana no se ofrece.
        $visit = $this->visit(self::MONDAY, '09:00', '10:30');

        $labels = $this->labelsFor($visit, '2026-08-04');

        $this->assertNotContains('08:00', $labels);
        $this->assertNotContains('09:30', $labels);
        $this->assertContains('10:00', $labels);
    }

    public function test_hoy_mismo_queda_fuera_por_la_antelacion(): void
    {
        $visit = $this->visit(self::MONDAY, '09:00', '10:30');

        $day = $this->dayFor($visit, '2026-08-03');

        $this->assertSame(0, $day['slot_count']);
        $this->assertSame('antelacion', $day['reason']);
    }

    public function test_un_dia_sin_huecos_devuelve_lista_vacia_con_motivo(): void
    {
        $moving = $this->visit(self::MONDAY, '09:00', '10:30');
        // Dos visitas que, con el colchon, no dejan hueco para 90 minutos.
        $this->visit(self::MONDAY, '08:00', '12:30');
        $this->visit(self::MONDAY, '13:30', '18:00');

        $day = $this->dayFor($moving, self::MONDAY);

        $this->assertSame(0, $day['slot_count']);
        $this->assertSame('agenda_llena', $day['reason']);
    }

    public function test_la_antelacion_sale_de_la_configuracion(): void
    {
        ScheduleSetting::put('min_reschedule_notice_hours', 96);

        $visit = $this->visit(self::MONDAY, '09:00', '10:30');

        // Con 96 h desde el lunes 10:00, el jueves entero queda fuera.
        $this->assertSame(0, $this->dayFor($visit, '2026-08-06')['slot_count']);
        $this->assertNotSame(0, $this->dayFor($visit, '2026-08-07')['slot_count']);
    }

    /**
     * La invariante de la fase: la disponibilidad puede ser mas estricta que el
     * validador, pero jamas mas laxa. Si esto se rompe, el cliente elige un chip y
     * recibe un 422 sin entender por que.
     */
    public function test_todo_espacio_ofrecido_pasa_la_validacion_del_backend(): void
    {
        $moving = $this->visit(self::MONDAY, '09:00', '10:30');
        $this->visit('2026-08-11', '08:00', '09:30');
        $this->visit('2026-08-12', '15:00', '16:30');
        $this->visit('2026-08-13', '11:00', '12:00');

        $from = CarbonImmutable::parse(self::MONDAY);
        $result = $this->availability->forVisit($moving, $from, $from->addDays(6));

        $checked = 0;

        foreach ($result['slots'] as $daySlots) {
            foreach ($daySlots as $slot) {
                $this->schedule->assertSlotIsFree(
                    $this->technician,
                    CarbonImmutable::parse($slot['value']),
                    CarbonImmutable::parse($slot['end_value']),
                    $moving->id,
                );

                $checked++;
            }
        }

        $this->assertGreaterThan(20, $checked, 'La muestra deberia cubrir varios dias.');
    }

    public function test_una_propuesta_fuera_de_la_rejilla_se_rechaza(): void
    {
        $visit = $this->visit(self::MONDAY, '09:00', '10:30');

        $this->expectException(ValidationException::class);

        // Dentro de la jornada pero desalineada del paso de 30 minutos.
        $this->availability->assertProposalIsOffered(
            $visit,
            CarbonImmutable::parse(self::MONDAY.' 09:07'),
        );
    }

    public function test_una_propuesta_ofrecida_se_acepta(): void
    {
        $visit = $this->visit(self::MONDAY, '09:00', '10:30');

        $this->availability->assertProposalIsOffered(
            $visit,
            CarbonImmutable::parse(self::MONDAY.' 14:00'),
        );

        $this->assertTrue(true);
    }

    public function test_la_duracion_del_hueco_es_la_de_la_visita_no_la_del_equipo(): void
    {
        // Visita de tres horas: el ultimo arranque posible es a las 15:00.
        $visit = $this->visit(self::MONDAY, '09:00', '12:00');

        $labels = $this->labelsFor($visit, self::MONDAY);

        $this->assertSame('15:00', end($labels));
        // 11:00-14:00 cruzaria el descanso.
        $this->assertNotContains('11:00', $labels);
    }
}
