<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Company;
use App\Models\Equipment;
use App\Models\ScheduledVisit;
use App\Models\ServiceReport;
use App\Models\Site;
use App\Models\TechnicianCheckin;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Database\Seeders\ScheduleSettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Quien llega a que (revision de accesos del 2026-11-18):
 *
 *  - Tecnico: solo lo suyo. Sus reportes, sus check-ins y los equipos que
 *    atiende. Nada de clientes, estadisticas, exportaciones ni tarjetas.
 *  - Usuario del cliente (rol admin): solo lo de su cliente, tambien al firmar
 *    y al pedir el PDF por correo. Sin cliente asignado no ve nada.
 *  - Oficina (master, super, coordinator): todo, como antes.
 */
class RoleAccessTest extends TestCase
{
    use RefreshDatabase;

    private User $tech;

    private User $otherTech;

    private User $coordinator;

    private User $clientUser;

    private Client $client;

    private Client $otherClient;

    private Equipment $equipment;

    private Equipment $otherEquipment;

    private ServiceReport $mine;

    private ServiceReport $theirs;

    private const SIGNATURE = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg==';

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([PermissionSeeder::class, RoleSeeder::class, ScheduleSettingSeeder::class]);
        Notification::fake();

        $this->client = Client::create(['business_name' => 'Edificio Central', 'nit' => '900123456-1']);
        $this->otherClient = Client::create(['business_name' => 'Torre Ajena', 'nit' => '900999999-9']);

        $this->tech = $this->user('technician', 'Técnico Uno');
        $this->otherTech = $this->user('technician', 'Técnico Dos');
        $this->coordinator = $this->user('coordinator', 'Coordinación');
        $this->clientUser = $this->user('admin', 'Usuario Cliente', ['client_id' => $this->client->id]);

        $this->equipment = $this->equipmentFor($this->client, 'TZ-A-0001');
        $this->otherEquipment = $this->equipmentFor($this->otherClient, 'TZ-B-0001');

        // El reporte del técnico uno, en su cliente; y el de otro técnico, en otro cliente.
        $this->mine = $this->report('RSTP-ACC-1', $this->equipment, $this->tech);
        $this->theirs = $this->report('RSTP-ACC-2', $this->otherEquipment, $this->otherTech);
    }

    private function user(string $role, string $name, array $extra = []): User
    {
        $user = User::factory()->create(['name' => $name, 'active' => true] + $extra);
        $user->assignRole($role);

        return $user;
    }

    private function equipmentFor(Client $client, string $code): Equipment
    {
        $site = Site::create(['client_id' => $client->id, 'name' => 'Sede '.$code, 'address' => 'Calle 1']);

        return Equipment::create(['site_id' => $site->id, 'internal_code' => $code, 'equipment_type' => 'ascensor'])->load('site');
    }

    private function report(string $number, Equipment $equipment, User $technician, string $status = 'firmado_tecnico'): ServiceReport
    {
        return ServiceReport::create([
            'report_number' => $number,
            'report_type' => 'RSTP',
            'equipment_id' => $equipment->id,
            'client_id' => $equipment->site->client_id,
            'site_id' => $equipment->site_id,
            'service_date' => '2026-11-18',
            'technician_id' => $technician->id,
            'status' => $status,
            'created_by' => $technician->id,
        ]);
    }

    // ── Técnico ─────────────────────────────────────────────────────────────

    public function test_el_tecnico_solo_lista_y_abre_sus_reportes(): void
    {
        Sanctum::actingAs($this->tech);

        $list = $this->getJson('/api/service-reports')->assertOk()->json();
        $this->assertSame(['RSTP-ACC-1'], array_column($list, 'report_number'));

        $this->getJson("/api/service-reports/{$this->mine->id}")->assertOk();
        $this->getJson("/api/service-reports/{$this->theirs->id}")->assertForbidden();
    }

    public function test_el_tecnico_no_toca_el_reporte_de_otro(): void
    {
        Sanctum::actingAs($this->tech);
        $id = $this->theirs->id;

        $this->getJson("/api/service-reports/{$id}/pdf")->assertForbidden();
        $this->postJson("/api/service-reports/{$id}/send-email")->assertForbidden();
        $this->postJson("/api/service-reports/{$id}/sign-technician", ['signature' => self::SIGNATURE])->assertForbidden();
        $this->postJson("/api/service-reports/{$id}/sign-customer", [
            'signature' => self::SIGNATURE, 'signer_name' => 'X', 'signer_document' => '1',
        ])->assertForbidden();
        $this->putJson("/api/service-reports/{$id}", [
            'report_type' => 'RSTP', 'equipment_id' => $this->otherEquipment->id, 'service_date' => '2026-11-18',
        ])->assertForbidden();

        $this->assertSame('firmado_tecnico', $this->theirs->fresh()->status);
    }

    public function test_el_segundo_tecnico_de_un_reporte_tambien_lo_ve(): void
    {
        $this->theirs->update(['secondary_technician_id' => $this->tech->id]);

        Sanctum::actingAs($this->tech);
        $this->getJson("/api/service-reports/{$this->theirs->id}")->assertOk();
    }

    public function test_el_tecnico_no_entra_a_clientes_estadisticas_exportacion_ni_tarjetas(): void
    {
        Sanctum::actingAs($this->tech);

        $this->getJson('/api/clients')->assertForbidden();
        $this->getJson("/api/clients/{$this->client->id}")->assertForbidden();
        $this->getJson("/api/clients/{$this->client->id}/sites")->assertForbidden();
        $this->getJson('/api/stats/overview')->assertForbidden();
        $this->getJson('/api/stats/technician-workload')->assertForbidden();
        $this->getJson('/api/service-reports-export')->assertForbidden();
        $this->getJson('/api/companies/'.Company::create(['user_id' => $this->coordinator->id, 'name' => 'Tzion', 'slug' => 'tzion-'.uniqid()])->id)->assertForbidden();
        $this->getJson('/api/suppliers')->assertForbidden();
        $this->getJson('/api/schedule/visits')->assertForbidden();
        $this->getJson('/api/admin/dashboard')->assertForbidden();
    }

    public function test_el_tecnico_solo_ve_los_equipos_que_atiende(): void
    {
        Sanctum::actingAs($this->tech);

        // Tiene un reporte en el primero; el segundo no es suyo.
        $list = $this->getJson('/api/equipment')->assertOk()->json();
        $this->assertSame(['TZ-A-0001'], array_column($list['data'] ?? $list, 'internal_code'));

        $this->getJson("/api/equipment/{$this->equipment->id}")->assertOk();
        $this->getJson("/api/equipment/{$this->otherEquipment->id}")->assertForbidden();
        $this->getJson("/api/equipment/{$this->otherEquipment->id}/history")->assertForbidden();
    }

    public function test_una_visita_asignada_o_un_checkin_le_abren_el_equipo(): void
    {
        $third = $this->equipmentFor($this->otherClient, 'TZ-C-0001');

        ScheduledVisit::create([
            'equipment_id' => $this->otherEquipment->id,
            'site_id' => $this->otherEquipment->site_id,
            'client_id' => $this->otherClient->id,
            'technician_id' => $this->tech->id,
            'visit_type' => 'preventivo',
            'scheduled_start' => '2026-11-19 09:00:00',
            'scheduled_end' => '2026-11-19 10:00:00',
            'duration_minutes' => 60,
            'status' => 'programada',
            'created_by' => $this->coordinator->id,
        ]);
        TechnicianCheckin::create([
            'equipment_id' => $third->id,
            'technician_id' => $this->tech->id,
            'checked_in_at' => now(),
            'method' => 'manual',
        ]);

        Sanctum::actingAs($this->tech);
        $this->getJson("/api/equipment/{$this->otherEquipment->id}")->assertOk();
        $this->getJson("/api/equipment/{$third->id}")->assertOk();
    }

    public function test_el_tecnico_no_recibe_la_ficha_comercial_del_cliente(): void
    {
        $this->client->update(['contact_email' => 'gerencia@cliente.test', 'notes' => 'Cartera en mora']);

        Sanctum::actingAs($this->tech);
        $client = $this->getJson("/api/equipment/{$this->equipment->id}")->assertOk()->json('site.client');

        $this->assertSame('Edificio Central', $client['business_name']);
        $this->assertArrayNotHasKey('contact_email', $client);
        $this->assertArrayNotHasKey('notes', $client);
        $this->assertArrayNotHasKey('nit', $client);
    }

    public function test_en_el_historial_del_equipo_el_tecnico_solo_ve_sus_reportes(): void
    {
        $this->report('RSTP-ACC-3', $this->equipment, $this->otherTech);

        Sanctum::actingAs($this->tech);
        $history = $this->getJson("/api/equipment/{$this->equipment->id}/history")->assertOk()->json();
        $this->assertSame(['RSTP-ACC-1'], array_column($history, 'report_number'));

        // La oficina sigue viendo todos.
        Sanctum::actingAs($this->coordinator);
        $this->assertCount(2, $this->getJson("/api/equipment/{$this->equipment->id}/history")->assertOk()->json());
    }

    public function test_el_tecnico_solo_ve_sus_checkins(): void
    {
        $own = TechnicianCheckin::create(['equipment_id' => $this->equipment->id, 'technician_id' => $this->tech->id, 'checked_in_at' => now(), 'method' => 'manual']);
        $foreign = TechnicianCheckin::create(['equipment_id' => $this->otherEquipment->id, 'technician_id' => $this->otherTech->id, 'checked_in_at' => now(), 'method' => 'manual']);

        Sanctum::actingAs($this->tech);
        $this->assertSame([$own->id], array_column($this->getJson('/api/tech/checkins')->assertOk()->json('data'), 'id'));
        $this->getJson("/api/tech/checkins/{$foreign->id}")->assertForbidden();
    }

    // ── Usuario del cliente ─────────────────────────────────────────────────

    public function test_el_cliente_solo_llega_a_lo_de_su_empresa(): void
    {
        Sanctum::actingAs($this->clientUser);

        $this->assertSame(['RSTP-ACC-1'], array_column($this->getJson('/api/service-reports')->assertOk()->json(), 'report_number'));
        $this->getJson("/api/service-reports/{$this->mine->id}")->assertOk();
        $this->getJson("/api/service-reports/{$this->theirs->id}")->assertForbidden();
        $this->getJson("/api/equipment/{$this->equipment->id}")->assertOk();
        $this->getJson("/api/equipment/{$this->otherEquipment->id}")->assertForbidden();
        $this->getJson("/api/clients/{$this->otherClient->id}")->assertForbidden();
        $this->assertCount(1, $this->getJson('/api/clients')->assertOk()->json());
    }

    public function test_el_cliente_no_se_envia_ni_firma_el_reporte_de_otro_cliente(): void
    {
        Sanctum::actingAs($this->clientUser);
        $id = $this->theirs->id;

        $this->postJson("/api/service-reports/{$id}/send-email")->assertForbidden();
        $this->getJson("/api/service-reports/{$id}/pdf")->assertForbidden();
        $this->postJson("/api/service-reports/{$id}/sign-customer", [
            'signature' => self::SIGNATURE, 'signer_name' => 'X', 'signer_document' => '1',
        ])->assertForbidden();

        Notification::assertNothingSent();
        $this->assertSame('firmado_tecnico', $this->theirs->fresh()->status);
    }

    public function test_el_cliente_no_entra_a_las_rutas_del_tecnico_ni_a_tarjetas(): void
    {
        Sanctum::actingAs($this->clientUser);

        $this->getJson('/api/tech/checkins')->assertForbidden();
        $this->getJson('/api/tech/visits/pending-signature')->assertForbidden();
        $this->getJson('/api/tech/schedule')->assertForbidden();
        $this->getJson('/api/companies/'.Company::create(['user_id' => $this->coordinator->id, 'name' => 'Tzion', 'slug' => 'tzion-'.uniqid()])->id)->assertForbidden();
        $this->getJson('/api/schedule/visits')->assertForbidden();
    }

    /** Antes este caso se quedaba sin filtro y veía los datos de todos los clientes. */
    public function test_un_usuario_de_cliente_sin_cliente_asignado_no_ve_nada(): void
    {
        Sanctum::actingAs($this->user('admin', 'Cliente Huérfano'));

        $this->assertSame([], $this->getJson('/api/service-reports')->assertOk()->json());
        $this->getJson("/api/service-reports/{$this->mine->id}")->assertForbidden();
        $this->getJson('/api/clients')->assertForbidden();
        $list = $this->getJson('/api/equipment')->assertOk()->json();
        $this->assertSame([], $list['data'] ?? $list);
        $this->getJson("/api/equipment/{$this->equipment->id}")->assertForbidden();
        // Las estadísticas le salen vacías, no con las cifras de toda la operación.
        $this->assertSame([], $this->getJson('/api/stats/technician-workload?date_from=2026-01-01&date_to=2026-12-31')->assertOk()->json());
    }

    // ── Oficina ─────────────────────────────────────────────────────────────

    public function test_coordinacion_sigue_viendo_todo(): void
    {
        Sanctum::actingAs($this->coordinator);

        $this->assertCount(2, $this->getJson('/api/service-reports')->assertOk()->json());
        $this->getJson("/api/service-reports/{$this->theirs->id}")->assertOk();
        $this->assertCount(2, $this->getJson('/api/clients')->assertOk()->json());
        $this->getJson("/api/equipment/{$this->otherEquipment->id}")->assertOk();
        $this->assertCount(2, $this->getJson('/api/stats/technician-workload?date_from=2026-01-01&date_to=2026-12-31')->assertOk()->json());
        $this->getJson('/api/tech/checkins')->assertOk();
    }
}
