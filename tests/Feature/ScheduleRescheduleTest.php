<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Equipment;
use App\Models\RescheduleRequest;
use App\Models\ScheduledVisit;
use App\Models\ScheduleSetting;
use App\Models\Site;
use App\Models\User;
use App\Models\VisitReminder;
use App\Notifications\RescheduleRequestedNotification;
use Carbon\CarbonImmutable;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Database\Seeders\ScheduleSettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Fase 4: el cliente pide mover su visita y coordinacion decide.
 *
 * Tiempo congelado un lunes a las 10:00 y visitas la semana siguiente, para que
 * la antelacion minima de 24 h no dependa del dia en que se corra la suite.
 */
class ScheduleRescheduleTest extends TestCase
{
    use RefreshDatabase;

    private User $coordinator;

    private User $master;

    private User $technician;

    private User $clientAdmin;

    private Equipment $equipment;

    private const NOW = '2026-08-03 10:00:00';

    /** Lunes de la semana siguiente. */
    private const VISIT_DAY = '2026-08-10';

    /** Martes de la semana siguiente: destino habitual de las propuestas. */
    private const TARGET_DAY = '2026-08-11';

    protected function setUp(): void
    {
        parent::setUp();

        CarbonImmutable::setTestNow(self::NOW);
        $this->travelTo(self::NOW);

        $this->seed([PermissionSeeder::class, RoleSeeder::class, ScheduleSettingSeeder::class]);

        $this->coordinator = User::factory()->create();
        $this->coordinator->assignRole('coordinator');

        $this->master = User::factory()->create();
        $this->master->assignRole('master');

        $this->technician = User::factory()->create(['name' => 'Técnico Uno']);
        $this->technician->assignRole('technician');

        $client = Client::create(['business_name' => 'Edificio Central', 'nit' => '900123456-1']);
        $site = Site::create(['client_id' => $client->id, 'name' => 'Torre A', 'address' => 'Calle 1']);

        $this->equipment = Equipment::create([
            'site_id' => $site->id,
            'internal_code' => 'TZ-A-0001',
            'equipment_type' => 'ascensor',
        ]);

        $this->clientAdmin = User::factory()->create(['client_id' => $client->id]);
        $this->clientAdmin->assignRole('admin');
    }

    protected function tearDown(): void
    {
        CarbonImmutable::setTestNow();

        parent::tearDown();
    }

    private function visit(array $extra = []): ScheduledVisit
    {
        return ScheduledVisit::create(array_merge([
            'equipment_id' => $this->equipment->id,
            'site_id' => $this->equipment->site_id,
            'client_id' => $this->equipment->site->client_id,
            'technician_id' => $this->technician->id,
            'scheduled_start' => self::VISIT_DAY.' 09:00',
            'scheduled_end' => self::VISIT_DAY.' 10:30',
            'status' => 'programada',
            'created_by' => $this->coordinator->id,
        ], $extra));
    }

    private function request(ScheduledVisit $visit, string $start = self::TARGET_DAY.' 09:00', array $extra = [])
    {
        Sanctum::actingAs($this->clientAdmin);

        return $this->postJson("/api/portal/schedule/{$visit->id}/reschedule-request", array_merge([
            'proposed_start' => $start,
        ], $extra));
    }

    // ── Solicitar ──

    public function test_el_cliente_solicita_reprogramacion_y_la_visita_queda_en_ambar(): void
    {
        Notification::fake();

        $visit = $this->visit();

        $this->request($visit, self::TARGET_DAY.' 09:00', ['reason' => 'Fumigación del edificio'])
            ->assertCreated()
            ->assertJsonPath('request.status', 'pendiente')
            ->assertJsonPath('visit.status', 'reprogramacion_solicitada');

        $request = RescheduleRequest::first();

        $this->assertSame($this->clientAdmin->id, $request->requested_by);
        $this->assertSame('Fumigación del edificio', $request->reason);
        $this->assertSame(self::VISIT_DAY.' 09:00', $request->original_start->format('Y-m-d H:i'));
        $this->assertSame(self::TARGET_DAY.' 09:00', $request->proposed_start->format('Y-m-d H:i'));
        // La duracion se conserva: el cliente propone cuando, no cuanto.
        $this->assertSame(self::TARGET_DAY.' 10:30', $request->proposed_end->format('Y-m-d H:i'));
        // Y la visita no se ha movido.
        $this->assertSame(self::VISIT_DAY.' 09:00', $visit->refresh()->scheduled_start->format('Y-m-d H:i'));
    }

    public function test_no_deja_dos_solicitudes_pendientes_sobre_la_misma_visita(): void
    {
        Notification::fake();

        $visit = $this->visit();

        $this->request($visit)->assertCreated();
        $this->request($visit, self::TARGET_DAY.' 14:00')
            ->assertStatus(422)
            ->assertJsonValidationErrors('visit');

        $this->assertSame(1, RescheduleRequest::count());
    }

    #[DataProvider('estadosNoReprogramables')]
    public function test_solo_se_puede_solicitar_sobre_visitas_programadas(string $status): void
    {
        Notification::fake();

        $visit = $this->visit(['status' => $status]);

        $this->request($visit)->assertStatus(422)->assertJsonValidationErrors('visit');

        $this->assertSame(0, RescheduleRequest::count());
    }

    public static function estadosNoReprogramables(): array
    {
        return [
            'en curso' => ['en_curso'],
            'completada' => ['completada'],
            'cancelada' => ['cancelada'],
            'no realizada' => ['no_realizada'],
            'ya solicitada' => ['reprogramacion_solicitada'],
        ];
    }

    public function test_rechaza_una_solicitud_con_menos_de_24_horas_de_antelacion(): void
    {
        Notification::fake();

        $visit = $this->visit();

        // Mañana a las 09:00: son menos de 24 h desde el lunes a las 10:00.
        $this->request($visit, '2026-08-04 09:00')
            ->assertStatus(422)
            ->assertJsonValidationErrors('proposed_start');
    }

    public function test_respeta_la_antelacion_configurada_en_schedule_settings(): void
    {
        Notification::fake();

        ScheduleSetting::put('min_reschedule_notice_hours', 24 * 10);

        $visit = $this->visit();

        $this->request($visit)->assertStatus(422)->assertJsonValidationErrors('proposed_start');
    }

    public function test_no_acepta_una_propuesta_fuera_de_la_jornada(): void
    {
        Notification::fake();

        $this->request($this->visit(), self::TARGET_DAY.' 19:00')
            ->assertStatus(422)
            ->assertJsonValidationErrors('proposed_start');
    }

    public function test_no_acepta_una_propuesta_que_pisa_el_descanso(): void
    {
        Notification::fake();

        $this->request($this->visit(), self::TARGET_DAY.' 12:30')
            ->assertStatus(422)
            ->assertJsonValidationErrors('proposed_start');
    }

    public function test_no_acepta_una_propuesta_en_dia_no_laborable(): void
    {
        Notification::fake();

        // 2026-08-15 es sábado.
        $this->request($this->visit(), '2026-08-15 09:00')
            ->assertStatus(422)
            ->assertJsonValidationErrors('proposed_start');
    }

    public function test_no_acepta_una_propuesta_que_choca_con_otra_visita_del_tecnico(): void
    {
        Notification::fake();

        $visit = $this->visit();
        $this->visit(['scheduled_start' => self::TARGET_DAY.' 09:00', 'scheduled_end' => self::TARGET_DAY.' 10:30']);

        $this->request($visit, self::TARGET_DAY.' 09:00')
            ->assertStatus(422)
            ->assertJsonValidationErrors('proposed_start');
    }

    public function test_no_acepta_una_propuesta_pegada_a_otra_visita_por_el_buffer_de_viaje(): void
    {
        Notification::fake();

        $visit = $this->visit();
        $this->visit(['scheduled_start' => self::TARGET_DAY.' 09:00', 'scheduled_end' => self::TARGET_DAY.' 10:00']);

        // 10:00 no solapa, pero cae dentro del colchon de 30 minutos: la
        // disponibilidad no lo ofrece, asi que tampoco se acepta.
        $this->request($visit, self::TARGET_DAY.' 10:00')
            ->assertStatus(422)
            ->assertJsonValidationErrors('proposed_start');
    }

    public function test_no_acepta_un_horario_desalineado_de_la_rejilla(): void
    {
        Notification::fake();

        $this->request($this->visit(), self::TARGET_DAY.' 09:07')
            ->assertStatus(422)
            ->assertJsonValidationErrors('proposed_start');
    }

    // ── Scoping ──

    public function test_un_cliente_no_puede_solicitar_sobre_la_visita_de_otro_cliente(): void
    {
        Notification::fake();

        $otherClient = Client::create(['business_name' => 'Otro', 'nit' => '900999999-9']);
        $otherAdmin = User::factory()->create(['client_id' => $otherClient->id]);
        $otherAdmin->assignRole('admin');

        $visit = $this->visit();

        Sanctum::actingAs($otherAdmin);

        $this->postJson("/api/portal/schedule/{$visit->id}/reschedule-request", [
            'proposed_start' => self::TARGET_DAY.' 09:00',
        ])->assertForbidden();
    }

    public function test_un_cliente_no_ve_la_disponibilidad_de_la_visita_de_otro(): void
    {
        $otherClient = Client::create(['business_name' => 'Otro', 'nit' => '900999999-9']);
        $otherAdmin = User::factory()->create(['client_id' => $otherClient->id]);
        $otherAdmin->assignRole('admin');

        $visit = $this->visit();

        Sanctum::actingAs($otherAdmin);

        $this->getJson("/api/portal/schedule/{$visit->id}/availability")->assertForbidden();
    }

    public function test_la_disponibilidad_del_portal_no_expone_las_visitas_ajenas(): void
    {
        $visit = $this->visit();

        Sanctum::actingAs($this->clientAdmin);

        $response = $this->getJson("/api/portal/schedule/{$visit->id}/availability")->assertOk();

        $response->assertJsonPath('can_request', true);
        $response->assertJsonPath('notice_hours', 24);
        $this->assertNotEmpty($response->json('days'));
        // Solo horarios: ni una pista de con quien o con que choca la agenda.
        $this->assertStringNotContainsString('TZ-A-0001', json_encode($response->json('slots')));
    }

    // ── Aviso a coordinacion ──

    public function test_avisa_a_coordinacion_cuando_el_cliente_solicita(): void
    {
        Notification::fake();

        $this->request($this->visit())->assertCreated();

        Notification::assertSentTo($this->coordinator, RescheduleRequestedNotification::class);
        Notification::assertSentTo($this->master, RescheduleRequestedNotification::class);
        // El tecnico no: mientras nadie apruebe, su agenda no cambia.
        Notification::assertNotSentTo($this->technician, RescheduleRequestedNotification::class);
        Notification::assertNotSentTo($this->clientAdmin, RescheduleRequestedNotification::class);
    }

    public function test_solicitar_no_toca_los_recordatorios_de_la_visita(): void
    {
        Notification::fake();

        $visit = $this->visit();

        VisitReminder::create([
            'scheduled_visit_id' => $visit->id,
            'user_id' => $this->clientAdmin->id,
            'send_at' => self::VISIT_DAY.' 08:00',
            'channels' => ['database', 'mail'],
        ]);

        $this->request($visit)->assertCreated();

        $this->assertSame(1, VisitReminder::where('status', 'pendiente')->count());
    }

    // ── Payload del portal ──

    public function test_el_portal_marca_las_visitas_que_se_pueden_reprogramar(): void
    {
        Notification::fake();

        $this->visit();

        Sanctum::actingAs($this->clientAdmin);

        $this->getJson('/api/portal/schedule')
            ->assertOk()
            ->assertJsonPath('upcoming.0.can_request_reschedule', true)
            ->assertJsonPath('upcoming.0.pending_reschedule_request', null);
    }

    public function test_el_portal_deja_de_ofrecer_el_boton_con_una_solicitud_pendiente(): void
    {
        Notification::fake();

        $visit = $this->visit();
        $this->request($visit)->assertCreated();

        Sanctum::actingAs($this->clientAdmin);

        $response = $this->getJson('/api/portal/schedule')->assertOk();

        $response->assertJsonPath('upcoming.0.can_request_reschedule', false);
        $this->assertNotNull($response->json('upcoming.0.pending_reschedule_request'));
    }

    public function test_el_portal_no_ofrece_reprogramar_dentro_de_la_antelacion(): void
    {
        Notification::fake();

        // Mañana: quedan menos de 24 h.
        $this->visit([
            'scheduled_start' => '2026-08-04 09:00',
            'scheduled_end' => '2026-08-04 10:30',
        ]);

        Sanctum::actingAs($this->clientAdmin);

        $this->getJson('/api/portal/schedule')
            ->assertOk()
            ->assertJsonPath('upcoming.0.can_request_reschedule', false);
    }
}
