<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Equipment;
use App\Models\EquipmentAccessUnlock;
use App\Models\ScheduledVisit;
use App\Models\ServiceReport;
use App\Models\Site;
use App\Models\TechnicianCheckin;
use App\Models\User;
use Carbon\CarbonImmutable;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Database\Seeders\ScheduleSettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Pedido del cliente (2026-11-08):
 *  - Bloqueo de llegada en preventivos: solo a la hora exacta; sin visita del dia
 *    tambien se bloquea; solo el master desbloquea.
 *  - Sin conexion: lo decide el telefono con su agenda; lo que no se pudo
 *    verificar llega marcado para revision.
 *  - Hora de entrada (check-in), de salida (guardado / firma) y de la llamada.
 */
class EquipmentAccessTest extends TestCase
{
    use RefreshDatabase;

    private User $technician;

    private User $master;

    private Equipment $equipment;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([PermissionSeeder::class, RoleSeeder::class, ScheduleSettingSeeder::class]);

        // Hora fija: el bloqueo depende del reloj.
        $this->travelTo(CarbonImmutable::parse('2026-11-09 08:00:00'));

        $this->technician = User::factory()->create(['name' => 'Técnico Uno', 'active' => true]);
        $this->technician->assignRole('technician');

        $this->master = User::factory()->create(['name' => 'Master', 'active' => true]);
        $this->master->assignRole('master');

        $client = Client::create(['business_name' => 'Edificio Central', 'nit' => '900123456-1']);
        $site = Site::create(['client_id' => $client->id, 'name' => 'Torre A', 'address' => 'Calle 1']);

        $this->equipment = Equipment::create([
            'site_id' => $site->id,
            'internal_code' => 'TZ-A-0001',
            'customer_code' => 'CLI-77',
            'equipment_type' => 'ascensor',
        ]);
    }

    private function visit(string $start, array $extra = []): ScheduledVisit
    {
        return ScheduledVisit::create(array_merge([
            'equipment_id' => $this->equipment->id,
            'site_id' => $this->equipment->site_id,
            'client_id' => $this->equipment->site->client_id,
            'technician_id' => $this->technician->id,
            'scheduled_start' => $start,
            'scheduled_end' => CarbonImmutable::parse($start)->addMinutes(90),
            'visit_type' => 'preventivo',
            'status' => 'programada',
            'created_by' => $this->master->id,
        ], $extra));
    }

    private function checkin(array $extra = [])
    {
        Sanctum::actingAs($this->technician);

        return $this->postJson('/api/tech/checkin', array_merge([
            'equipment_code' => $this->equipment->internal_code,
            'method' => 'qr',
        ], $extra));
    }

    // ── Bloqueo con conexion ──

    public function test_preventivo_antes_de_la_hora_se_bloquea_y_avisa_al_master(): void
    {
        Notification::fake();
        Event::fake([\App\Events\EquipmentAccessBlocked::class]);
        $visit = $this->visit('2026-11-09 10:00:00');

        $this->checkin()
            ->assertStatus(423)
            ->assertJsonPath('code', 'access_locked')
            ->assertJsonPath('reason', 'anticipado');

        $this->assertDatabaseCount('technician_checkins', 0);
        $this->assertSame('programada', $visit->fresh()->status);
        Notification::assertSentTo($this->master, \App\Notifications\EquipmentAccessBlockedNotification::class);
    }

    public function test_el_aviso_de_bloqueo_no_se_repite_en_cada_intento(): void
    {
        Notification::fake();
        Event::fake([\App\Events\EquipmentAccessBlocked::class]);
        $this->visit('2026-11-09 10:00:00');

        $this->checkin()->assertStatus(423);
        $this->checkin()->assertStatus(423);

        Notification::assertSentToTimes($this->master, \App\Notifications\EquipmentAccessBlockedNotification::class, 1);
    }

    public function test_preventivo_a_la_hora_exacta_entra(): void
    {
        $visit = $this->visit('2026-11-09 08:00:00');

        $this->checkin()->assertCreated()->assertJsonPath('checkin.access_status', 'a_tiempo');

        $checkin = TechnicianCheckin::first();
        $this->assertSame($visit->id, $checkin->scheduled_visit_id);
        $this->assertFalse($checkin->needs_review);
        $this->assertSame('en_curso', $visit->fresh()->status);
    }

    public function test_un_minuto_antes_sigue_bloqueado(): void
    {
        $this->visit('2026-11-09 08:01:00');

        $this->checkin()->assertStatus(423);
    }

    public function test_sin_visita_del_dia_se_bloquea(): void
    {
        // Visita de manana: no habilita hoy.
        $this->visit('2026-11-10 08:00:00');

        $this->checkin()->assertStatus(423)->assertJsonPath('reason', 'sin_programar');
    }

    public function test_la_visita_de_otro_tecnico_no_habilita(): void
    {
        $otro = User::factory()->create();
        $otro->assignRole('technician');
        $this->visit('2026-11-09 07:00:00', ['technician_id' => $otro->id]);

        $this->checkin()->assertStatus(423)->assertJsonPath('reason', 'sin_programar');
    }

    public function test_correctivo_programado_entra_a_cualquier_hora(): void
    {
        $this->visit('2026-11-09 15:00:00', ['visit_type' => 'correctivo']);

        $this->checkin()->assertCreated();
    }

    public function test_la_emergencia_nunca_se_bloquea(): void
    {
        $this->checkin([
            'is_emergency' => true,
            'call_received_at' => '2026-11-09 07:15:00',
        ])->assertCreated()->assertJsonPath('checkin.access_status', 'emergencia');

        // Antes salía negativo (Carbon 3 da diferencias con signo).
        $this->assertSame(45, TechnicianCheckin::first()->response_time_minutes);
    }

    public function test_el_codigo_del_cliente_tambien_sirve(): void
    {
        $this->visit('2026-11-09 07:30:00');

        $this->checkin(['equipment_code' => 'CLI-77'])->assertCreated();
    }

    // ── Desbloqueo del master ──

    public function test_el_master_desbloquea_el_ingreso_de_hoy(): void
    {
        $this->visit('2026-11-09 10:00:00');

        Sanctum::actingAs($this->master);
        $this->postJson("/api/equipment/{$this->equipment->id}/access-unlock", ['reason' => 'Cliente pidió adelantar'])
            ->assertCreated();

        $this->checkin()->assertCreated()->assertJsonPath('checkin.access_status', 'desbloqueado');
    }

    public function test_el_desbloqueo_tambien_habilita_un_equipo_sin_visita(): void
    {
        EquipmentAccessUnlock::create([
            'equipment_id' => $this->equipment->id,
            'unlock_date' => '2026-11-09',
            'unlocked_by' => $this->master->id,
        ]);

        $this->checkin()->assertCreated();
    }

    public function test_el_desbloqueo_de_ayer_no_sirve_hoy(): void
    {
        EquipmentAccessUnlock::create([
            'equipment_id' => $this->equipment->id,
            'unlock_date' => '2026-11-08',
            'unlocked_by' => $this->master->id,
        ]);

        $this->checkin()->assertStatus(423);
    }

    public function test_solo_el_master_puede_desbloquear(): void
    {
        foreach (['coordinator', 'super', 'technician'] as $role) {
            $user = User::factory()->create();
            $user->assignRole($role);
            Sanctum::actingAs($user);

            $this->postJson("/api/equipment/{$this->equipment->id}/access-unlock")->assertForbidden();
        }

        $this->assertDatabaseCount('equipment_access_unlocks', 0);
    }

    public function test_el_master_puede_volver_a_bloquear(): void
    {
        Sanctum::actingAs($this->master);
        $this->postJson("/api/equipment/{$this->equipment->id}/access-unlock")->assertCreated();
        $this->deleteJson("/api/equipment/{$this->equipment->id}/access-unlock")->assertOk();

        $this->checkin()->assertStatus(423);
    }

    // ── Sin conexion ──

    public function test_la_agenda_del_dia_trae_visitas_y_desbloqueos(): void
    {
        $this->visit('2026-11-09 10:00:00');
        $this->visit('2026-11-10 10:00:00');
        EquipmentAccessUnlock::create([
            'equipment_id' => $this->equipment->id,
            'unlock_date' => '2026-11-09',
            'unlocked_by' => $this->master->id,
        ]);

        Sanctum::actingAs($this->technician);
        $this->getJson('/api/tech/access-agenda')
            ->assertOk()
            ->assertJsonPath('date', '2026-11-09')
            ->assertJsonCount(1, 'visits')
            ->assertJsonPath('visits.0.internal_code', 'TZ-A-0001')
            ->assertJsonPath('visits.0.customer_code', 'CLI-77')
            ->assertJsonPath('unlocked.0.equipment_id', $this->equipment->id);
    }

    public function test_el_registro_sin_conexion_no_se_rechaza_pero_se_marca_si_no_cuadra(): void
    {
        $this->visit('2026-11-09 10:00:00');

        // Llegó a las 7:30 sin señal y subió a las 8:00.
        $this->checkin([
            'client_uuid' => (string) Str::uuid(),
            'checked_in_at' => '2026-11-09 07:30:00',
            'offline_check' => 'permitido',
        ])->assertCreated();

        $checkin = TechnicianCheckin::first();
        $this->assertSame('anticipado', $checkin->access_status);
        $this->assertTrue($checkin->needs_review);
    }

    public function test_sin_agenda_en_el_telefono_pasa_marcado_para_revision(): void
    {
        $this->visit('2026-11-09 07:00:00');

        $this->checkin([
            'client_uuid' => (string) Str::uuid(),
            'checked_in_at' => '2026-11-09 07:45:00',
            'offline_check' => 'sin_agenda',
        ])->assertCreated();

        $checkin = TechnicianCheckin::first();
        $this->assertSame('a_tiempo', $checkin->access_status);
        $this->assertSame('sin_agenda', $checkin->offline_check);
        $this->assertTrue($checkin->needs_review);
    }

    public function test_verificado_con_la_agenda_y_a_tiempo_no_se_marca(): void
    {
        $this->visit('2026-11-09 07:00:00');

        $this->checkin([
            'client_uuid' => (string) Str::uuid(),
            'checked_in_at' => '2026-11-09 07:45:00',
            'offline_check' => 'permitido',
        ])->assertCreated();

        $this->assertFalse(TechnicianCheckin::first()->needs_review);
    }

    public function test_reloj_del_telefono_adelantado_se_marca(): void
    {
        $this->visit('2026-11-09 10:00:00');

        // El telefono dice 10:05 pero en el servidor son las 8:00.
        $this->checkin([
            'client_uuid' => (string) Str::uuid(),
            'checked_in_at' => '2026-11-09 10:05:00',
            'offline_check' => 'permitido',
        ])->assertCreated();

        $checkin = TechnicianCheckin::first();
        $this->assertSame('reloj_inconsistente', $checkin->access_status);
        $this->assertTrue($checkin->needs_review);
    }

    // ── Hora de la llamada anotada por coordinacion ──

    public function test_la_hora_de_llamada_del_correctivo_programado_da_el_tiempo_de_respuesta(): void
    {
        $this->visit('2026-11-09 07:40:00', [
            'visit_type' => 'correctivo',
            'call_received_at' => '2026-11-09 07:20:00',
        ]);

        $this->checkin()->assertCreated();

        $checkin = TechnicianCheckin::first();
        $this->assertTrue($checkin->is_emergency);
        $this->assertSame(40, $checkin->response_time_minutes);
    }

    public function test_coordinacion_anota_la_hora_de_llamada_al_programar(): void
    {
        $coordinator = User::factory()->create();
        $coordinator->assignRole('coordinator');
        Sanctum::actingAs($coordinator);

        $this->postJson('/api/schedule/visits', [
            'equipment_id' => $this->equipment->id,
            'technician_id' => $this->technician->id,
            'scheduled_start' => '2026-11-09 09:00',
            'scheduled_end' => '2026-11-09 10:00',
            'visit_type' => 'correctivo',
            'call_received_at' => '2026-11-09 08:00',
        ])->assertCreated();

        $this->assertSame('2026-11-09 08:00', ScheduledVisit::first()->call_received_at->format('Y-m-d H:i'));
    }

    // ── Horas en el reporte ──

    public function test_el_reporte_toma_entrada_y_llamada_del_checkin_y_no_las_pierde_al_guardar(): void
    {
        $this->checkin([
            'is_emergency' => true,
            'call_received_at' => '2026-11-09 06:30:00',
        ])->assertCreated();
        $checkin = TechnicianCheckin::first();

        $report = $this->postJson('/api/service-reports', [
            'report_type' => 'RSTC',
            'equipment_id' => $this->equipment->id,
            'service_date' => '2026-11-09',
            'checkin_id' => $checkin->id,
        ])->assertCreated()->json();

        $this->assertSame('08:00', substr($report['time_in'], 0, 5));
        $this->assertSame($report['id'], $checkin->fresh()->service_report_id);

        // El formulario del tecnico guarda los detalles sin los tiempos.
        $this->putJson("/api/service-reports/{$report['id']}", [
            'report_type' => 'RSTC',
            'equipment_id' => $this->equipment->id,
            'service_date' => '2026-11-09',
            'customer_order_ref' => 'OC-1234',
            'time_out' => '09:15',
            'rstc_details' => ['fault_location' => 'maquinaria', 'analysis_notes' => 'x'],
        ])->assertOk();

        $fresh = ServiceReport::with('rstcDetails')->find($report['id']);
        $this->assertSame('OC-1234', $fresh->customer_order_ref);
        $this->assertSame('09:15', substr($fresh->time_out, 0, 5));
        $this->assertSame('2026-11-09 06:30', $fresh->rstcDetails->call_time->format('Y-m-d H:i'));
        $this->assertSame(1, $fresh->rstcDetails->response_time_hh);
        $this->assertSame(30, $fresh->rstcDetails->response_time_mm);
        $this->assertSame('maquinaria', $fresh->rstcDetails->fault_location);
    }

    public function test_el_reporte_sin_conexion_se_enlaza_por_el_uuid_del_checkin(): void
    {
        $this->visit('2026-11-09 07:00:00');
        $uuid = (string) Str::uuid();
        $this->checkin([
            'client_uuid' => $uuid,
            'checked_in_at' => '2026-11-09 07:10:00',
            'offline_check' => 'permitido',
        ])->assertCreated();

        $report = $this->postJson('/api/service-reports', [
            'client_uuid' => (string) Str::uuid(),
            'report_type' => 'RSTP',
            'equipment_id' => $this->equipment->id,
            'service_date' => '2026-11-09',
            'checkin_client_uuid' => $uuid,
            'time_out' => '07:55',
        ])->assertCreated()->json();

        $this->assertSame('07:10', substr($report['time_in'], 0, 5));
        $this->assertSame('07:55', substr($report['time_out'], 0, 5));
    }

    public function test_la_hora_guardada_con_segundos_se_acepta_al_reenviar(): void
    {
        $this->visit('2026-11-09 07:00:00');
        $this->checkin()->assertCreated();

        $report = $this->postJson('/api/service-reports', [
            'report_type' => 'RSTP',
            'equipment_id' => $this->equipment->id,
            'service_date' => '2026-11-09',
            'time_in' => '08:00:00',
        ])->assertCreated()->json();

        $this->putJson("/api/service-reports/{$report['id']}", [
            'report_type' => 'RSTP',
            'equipment_id' => $this->equipment->id,
            'service_date' => '2026-11-09',
            'time_in' => '08:00:00',
            'time_out' => '09:30:00',
        ])->assertOk();
    }

    public function test_el_comando_rellena_las_horas_de_los_reportes_viejos(): void
    {
        $this->visit('2026-11-09 07:00:00');
        $this->checkin()->assertCreated();

        $report = ServiceReport::create([
            'report_number' => 'RSTP-OLD-1',
            'report_type' => 'RSTP',
            'equipment_id' => $this->equipment->id,
            'client_id' => $this->equipment->site->client_id,
            'site_id' => $this->equipment->site_id,
            'service_date' => '2026-11-09',
            'technician_id' => $this->technician->id,
            'status' => 'cerrado',
            'customer_signed_at' => '2026-11-09 09:40:00',
            'created_by' => $this->technician->id,
        ]);

        $this->artisan('reports:backfill-times')->assertSuccessful();
        $this->assertNull($report->fresh()->time_in, 'Sin --apply no guarda nada');

        $this->artisan('reports:backfill-times', ['--apply' => true])->assertSuccessful();

        $fresh = $report->fresh();
        $this->assertSame('08:00', substr($fresh->time_in, 0, 5));
        $this->assertSame('09:40', substr($fresh->time_out, 0, 5));
    }

    /** El telefono manda la hora en UTC; antes se guardaba 5 h corrida. */
    public function test_la_hora_utc_del_telefono_se_guarda_en_hora_de_colombia(): void
    {
        $this->visit('2026-11-09 07:00:00');

        $this->checkin([
            'client_uuid' => (string) Str::uuid(),
            'checked_in_at' => '2026-11-09T12:45:00.000Z', // 7:45 en Bogota
            'offline_check' => 'permitido',
        ])->assertCreated();

        $checkin = TechnicianCheckin::first();
        $this->assertSame('2026-11-09 07:45', $checkin->checked_in_at->format('Y-m-d H:i'));
        $this->assertSame('a_tiempo', $checkin->access_status);
        $this->assertFalse($checkin->needs_review);
    }

    public function test_la_firma_en_lote_fija_la_salida_con_la_hora_del_telefono(): void
    {
        $visitUuid = (string) Str::uuid();
        $report = ServiceReport::create([
            'report_number' => 'RSTP-QA-1',
            'report_type' => 'RSTP',
            'visit_uuid' => $visitUuid,
            'equipment_id' => $this->equipment->id,
            'client_id' => $this->equipment->site->client_id,
            'site_id' => $this->equipment->site_id,
            'service_date' => '2026-11-09',
            'technician_id' => $this->technician->id,
            'status' => 'firmado_tecnico',
            'time_out' => '07:30',
            'created_by' => $this->technician->id,
        ]);

        Sanctum::actingAs($this->technician);
        $this->postJson('/api/tech/visits/sign-customer', [
            'visit_uuid' => $visitUuid,
            'signature' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg==',
            'signer_name' => 'Portería',
            'signer_document' => '123',
            'signed_at' => '2026-11-09T12:50:00.000Z', // 7:50 en Bogota
        ])->assertOk();

        $this->assertSame('07:50', substr($report->fresh()->time_out, 0, 5));
    }

    public function test_el_comando_corrige_la_hora_utc_de_los_checkins_viejos_sin_conexion(): void
    {
        // Asi quedaba antes del arreglo: 12:45 UTC guardado como si fuera local.
        $old = TechnicianCheckin::create([
            'equipment_id' => $this->equipment->id,
            'technician_id' => $this->technician->id,
            'client_uuid' => (string) Str::uuid(),
            'checked_in_at' => '2026-11-09 12:45:00',
            'method' => 'manual',
        ]);
        $online = TechnicianCheckin::create([
            'equipment_id' => $this->equipment->id,
            'technician_id' => $this->technician->id,
            'checked_in_at' => '2026-11-09 06:00:00',
            'method' => 'qr',
        ]);

        $this->artisan('reports:backfill-times', ['--apply' => true])->assertSuccessful();
        $this->artisan('reports:backfill-times', ['--apply' => true])->assertSuccessful();

        $this->assertSame('2026-11-09 07:45', $old->fresh()->checked_in_at->format('Y-m-d H:i'), 'se corrige una sola vez');
        $this->assertSame('2026-11-09 06:00', $online->fresh()->checked_in_at->format('Y-m-d H:i'));
    }
}
