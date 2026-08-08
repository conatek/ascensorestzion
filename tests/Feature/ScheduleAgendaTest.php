<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Equipment;
use App\Models\ScheduledVisit;
use App\Models\Site;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Database\Seeders\ScheduleSettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Fase 2: quien ve que. La agenda del tecnico y el cronograma del portal leen las
 * mismas visitas que el tablero de coordinacion, asi que lo unico que las separa
 * del resto de los datos es el scoping — y eso es lo que se prueba aqui.
 */
class ScheduleAgendaTest extends TestCase
{
    use RefreshDatabase;

    private User $technician;

    private User $otherTechnician;

    private User $clientAdmin;

    private Equipment $equipment;

    private Equipment $otherClientEquipment;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([PermissionSeeder::class, RoleSeeder::class, ScheduleSettingSeeder::class]);

        $this->technician = User::factory()->create(['name' => 'Técnico Uno']);
        $this->technician->assignRole('technician');

        $this->otherTechnician = User::factory()->create(['name' => 'Técnico Dos']);
        $this->otherTechnician->assignRole('technician');

        $this->equipment = $this->makeEquipment('Edificio Central', '900123456-1', 'TZ-A-0001');
        $this->otherClientEquipment = $this->makeEquipment('Otro Edificio', '900999999-9', 'TZ-B-0001');

        $this->clientAdmin = User::factory()->create([
            'client_id' => $this->equipment->site->client_id,
        ]);
        $this->clientAdmin->assignRole('admin');
    }

    private function makeEquipment(string $business, string $nit, string $code): Equipment
    {
        $client = Client::create(['business_name' => $business, 'nit' => $nit]);

        $site = Site::create([
            'client_id' => $client->id,
            'name' => "Sede de $business",
            'address' => 'Calle 1 # 2-3',
        ]);

        return Equipment::create([
            'site_id' => $site->id,
            'internal_code' => $code,
            'equipment_type' => 'ascensor',
        ]);
    }

    private function visit(Equipment $equipment, User $technician, string $start, array $extra = []): ScheduledVisit
    {
        return ScheduledVisit::create(array_merge([
            'equipment_id' => $equipment->id,
            'site_id' => $equipment->site_id,
            'client_id' => $equipment->site->client_id,
            'technician_id' => $technician->id,
            'scheduled_start' => $start,
            'scheduled_end' => \Carbon\CarbonImmutable::parse($start)->addMinutes(90),
            'visit_type' => 'preventivo',
            'status' => 'programada',
            'created_by' => $technician->id,
        ], $extra));
    }

    // ── Agenda del tecnico ──

    public function test_el_tecnico_solo_ve_sus_visitas(): void
    {
        $this->visit($this->equipment, $this->technician, '2026-08-03 09:00');
        $this->visit($this->equipment, $this->otherTechnician, '2026-08-03 11:00');

        Sanctum::actingAs($this->technician);

        $this->getJson('/api/tech/schedule?from=2026-08-03&to=2026-08-08')
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.technician_id', $this->technician->id);
    }

    public function test_la_agenda_del_tecnico_omite_las_canceladas(): void
    {
        $this->visit($this->equipment, $this->technician, '2026-08-03 09:00');
        $this->visit($this->equipment, $this->technician, '2026-08-04 09:00', ['status' => 'cancelada']);

        Sanctum::actingAs($this->technician);

        $this->getJson('/api/tech/schedule?from=2026-08-03&to=2026-08-08')
            ->assertOk()
            ->assertJsonCount(1);
    }

    public function test_el_tecnico_no_abre_el_detalle_de_una_visita_ajena(): void
    {
        $ajena = $this->visit($this->equipment, $this->otherTechnician, '2026-08-03 09:00');

        Sanctum::actingAs($this->technician);

        $this->getJson("/api/tech/schedule/{$ajena->id}")->assertForbidden();
    }

    public function test_el_detalle_trae_la_sede_con_geo_y_el_ultimo_reporte(): void
    {
        $visit = $this->visit($this->equipment, $this->technician, '2026-08-03 09:00');

        Sanctum::actingAs($this->technician);

        $this->getJson("/api/tech/schedule/{$visit->id}")
            ->assertOk()
            ->assertJsonPath('visit.id', $visit->id)
            ->assertJsonStructure(['visit' => ['site' => ['latitude', 'longitude', 'contact_phone_onsite']], 'last_report']);
    }

    public function test_la_agenda_exige_rango_de_fechas(): void
    {
        Sanctum::actingAs($this->technician);

        $this->getJson('/api/tech/schedule')
            ->assertStatus(422)
            ->assertJsonValidationErrors(['from', 'to']);
    }

    // ── Cronograma del portal ──

    public function test_el_cliente_solo_ve_las_visitas_de_sus_equipos(): void
    {
        $this->visit($this->equipment, $this->technician, '2099-01-05 09:00');
        $this->visit($this->otherClientEquipment, $this->technician, '2099-01-05 11:00');

        Sanctum::actingAs($this->clientAdmin);

        $this->getJson('/api/portal/schedule')
            ->assertOk()
            ->assertJsonCount(1, 'upcoming')
            ->assertJsonPath('upcoming.0.client_id', $this->equipment->site->client_id);
    }

    public function test_el_portal_separa_proximas_de_historial(): void
    {
        $this->visit($this->equipment, $this->technician, '2020-03-02 09:00', ['status' => 'completada']);
        $this->visit($this->equipment, $this->technician, '2099-01-05 09:00');

        Sanctum::actingAs($this->clientAdmin);

        $this->getJson('/api/portal/schedule')
            ->assertOk()
            ->assertJsonCount(1, 'upcoming')
            ->assertJsonCount(1, 'history');
    }

    /** Con rango la respuesta es plana: es la que consume la vista de calendario. */
    public function test_el_portal_devuelve_lista_plana_cuando_se_le_pasa_un_rango(): void
    {
        $this->visit($this->equipment, $this->technician, '2099-01-05 09:00');

        Sanctum::actingAs($this->clientAdmin);

        $this->getJson('/api/portal/schedule?from=2099-01-01&to=2099-02-01')
            ->assertOk()
            ->assertJsonCount(1);
    }

    public function test_el_cliente_no_abre_una_visita_de_otro_cliente(): void
    {
        $ajena = $this->visit($this->otherClientEquipment, $this->technician, '2099-01-05 09:00');

        Sanctum::actingAs($this->clientAdmin);

        $this->getJson("/api/portal/schedule/{$ajena->id}")->assertForbidden();
    }

    public function test_el_dashboard_del_portal_trae_la_proxima_visita(): void
    {
        $this->visit($this->equipment, $this->technician, '2099-01-09 09:00');
        $proxima = $this->visit($this->equipment, $this->technician, '2099-01-05 09:00');

        Sanctum::actingAs($this->clientAdmin);

        $this->getJson('/api/portal/dashboard')
            ->assertOk()
            ->assertJsonPath('next_visit.id', $proxima->id);
    }

    public function test_un_tecnico_no_entra_al_cronograma_del_portal(): void
    {
        Sanctum::actingAs($this->technician);

        $this->getJson('/api/portal/schedule')->assertForbidden();
    }
}
