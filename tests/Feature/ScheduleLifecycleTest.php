<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Equipment;
use App\Models\ScheduledVisit;
use App\Models\ServiceReport;
use App\Models\Site;
use App\Models\User;
use Carbon\CarbonImmutable;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Database\Seeders\ScheduleSettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Fase 2: la ejecucion mueve el cronograma.
 *
 * Es lo que convierte el plan en tablero de cumplimiento: el check-in arranca la
 * visita, la firma del cliente la cierra y lo que nadie ejecuto acaba en
 * no_realizada.
 */
class ScheduleLifecycleTest extends TestCase
{
    use RefreshDatabase;

    private User $technician;

    private Equipment $equipment;

    private Equipment $secondEquipment;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');

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

        $this->secondEquipment = Equipment::create([
            'site_id' => $site->id,
            'internal_code' => 'TZ-A-0002',
            'equipment_type' => 'ascensor',
        ]);
    }

    private function visit(Equipment $equipment, string $start, array $extra = []): ScheduledVisit
    {
        return ScheduledVisit::create(array_merge([
            'equipment_id' => $equipment->id,
            'site_id' => $equipment->site_id,
            'client_id' => $equipment->site->client_id,
            'technician_id' => $this->technician->id,
            'scheduled_start' => $start,
            'scheduled_end' => CarbonImmutable::parse($start)->addMinutes(90),
            'visit_type' => 'preventivo',
            'status' => 'programada',
            'created_by' => $this->technician->id,
        ], $extra));
    }

    private function report(Equipment $equipment, ?string $visitUuid, string $status = 'firmado_tecnico'): ServiceReport
    {
        return ServiceReport::create([
            'report_number' => 'RSTP-'.Str::random(6),
            'report_type' => 'RSTP',
            'visit_uuid' => $visitUuid,
            'equipment_id' => $equipment->id,
            'client_id' => $equipment->site->client_id,
            'site_id' => $equipment->site_id,
            'service_date' => now()->toDateString(),
            'technician_id' => $this->technician->id,
            'status' => $status,
            'created_by' => $this->technician->id,
        ]);
    }

    // ── Check-in → en_curso ──

    public function test_el_checkin_pone_la_visita_en_curso(): void
    {
        $visit = $this->visit($this->equipment, now()->setTime(9, 0)->toDateTimeString());

        Sanctum::actingAs($this->technician);

        $this->postJson('/api/tech/checkin', [
            'equipment_code' => $this->equipment->internal_code,
            'method' => 'manual',
        ])->assertCreated();

        $this->assertSame('en_curso', $visit->fresh()->status);
    }

    /** La visita de otro equipo de la misma sede no se arranca sola. */
    public function test_el_checkin_no_toca_la_visita_de_otro_equipo(): void
    {
        $otra = $this->visit($this->secondEquipment, now()->setTime(11, 0)->toDateTimeString());

        Sanctum::actingAs($this->technician);

        $this->postJson('/api/tech/checkin', [
            'equipment_code' => $this->equipment->internal_code,
            'method' => 'manual',
        ])->assertCreated();

        $this->assertSame('programada', $otra->fresh()->status);
    }

    /** Llegar tarde no es llegar a otra visita: el match es por dia, no por hora. */
    public function test_el_checkin_fuera_de_hora_arranca_la_visita_del_dia(): void
    {
        $visit = $this->visit($this->equipment, now()->setTime(8, 0)->toDateTimeString());

        Sanctum::actingAs($this->technician);

        $this->postJson('/api/tech/checkin', [
            'equipment_code' => $this->equipment->internal_code,
            'method' => 'manual',
            'checked_in_at' => now()->setTime(16, 30)->toDateTimeString(),
        ])->assertCreated();

        $this->assertSame('en_curso', $visit->fresh()->status);
    }

    public function test_un_checkin_sin_visita_programada_no_rompe_nada(): void
    {
        Sanctum::actingAs($this->technician);

        $this->postJson('/api/tech/checkin', [
            'equipment_code' => $this->equipment->internal_code,
            'method' => 'manual',
        ])->assertCreated();

        $this->assertSame(0, ScheduledVisit::count());
    }

    // ── Firma del cliente → completada ──

    public function test_la_firma_en_lote_cierra_las_visitas_de_todos_los_equipos(): void
    {
        $visitUuid = (string) Str::uuid();

        $a = $this->visit($this->equipment, now()->setTime(9, 0)->toDateTimeString(), [
            'status' => 'en_curso', 'visit_uuid' => $visitUuid,
        ]);
        $b = $this->visit($this->secondEquipment, now()->setTime(11, 0)->toDateTimeString(), [
            'status' => 'en_curso', 'visit_uuid' => $visitUuid,
        ]);

        $this->report($this->equipment, $visitUuid);
        $this->report($this->secondEquipment, $visitUuid);

        Sanctum::actingAs($this->technician);

        $this->postJson('/api/tech/visits/sign-customer', [
            'visit_uuid' => $visitUuid,
            'signature' => 'data:image/png;base64,'.base64_encode('firma'),
            'signer_name' => 'Ana Pérez',
            'signer_document' => '123456',
        ])->assertOk();

        $this->assertSame('completada', $a->fresh()->status);
        $this->assertSame('completada', $b->fresh()->status);
    }

    /** Sin visit_uuid enlazado todavia se cierra por equipo, tecnico y dia. */
    public function test_la_firma_cierra_la_visita_aunque_no_se_haya_enlazado_el_uuid(): void
    {
        $visit = $this->visit($this->equipment, now()->setTime(9, 0)->toDateTimeString(), ['status' => 'en_curso']);

        $visitUuid = (string) Str::uuid();
        $this->report($this->equipment, $visitUuid);

        Sanctum::actingAs($this->technician);

        $this->postJson('/api/tech/visits/sign-customer', [
            'visit_uuid' => $visitUuid,
            'signature' => 'data:image/png;base64,'.base64_encode('firma'),
            'signer_name' => 'Ana Pérez',
            'signer_document' => '123456',
        ])->assertOk();

        $this->assertSame('completada', $visit->fresh()->status);
    }

    public function test_la_firma_individual_tambien_cierra_la_visita(): void
    {
        $visit = $this->visit($this->equipment, now()->setTime(9, 0)->toDateTimeString(), ['status' => 'en_curso']);
        $report = $this->report($this->equipment, null);

        Sanctum::actingAs($this->technician);

        $this->postJson("/api/service-reports/{$report->id}/sign-customer", [
            'signature' => 'data:image/png;base64,'.base64_encode('firma'),
            'signer_name' => 'Ana Pérez',
            'signer_document' => '123456',
        ])->assertOk();

        $this->assertSame('completada', $visit->fresh()->status);
    }

    // ── Lo que nadie ejecuto ──

    public function test_el_comando_marca_no_realizadas_las_visitas_pasadas(): void
    {
        $vieja = $this->visit($this->equipment, now()->subDays(3)->setTime(9, 0)->toDateTimeString());
        $futura = $this->visit($this->equipment, now()->addDays(3)->setTime(9, 0)->toDateTimeString());

        $this->artisan('visits:mark-overdue')->assertSuccessful();

        $this->assertSame('no_realizada', $vieja->fresh()->status);
        $this->assertSame('programada', $futura->fresh()->status);
    }

    /** Alguien estuvo en sitio: eso es una firma pendiente, no un incumplimiento. */
    public function test_el_comando_respeta_las_visitas_en_curso(): void
    {
        $enCurso = $this->visit($this->equipment, now()->subDays(3)->setTime(9, 0)->toDateTimeString(), [
            'status' => 'en_curso',
        ]);

        $this->artisan('visits:mark-overdue')->assertSuccessful();

        $this->assertSame('en_curso', $enCurso->fresh()->status);
    }
}
