<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Equipment;
use App\Models\Site;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * El borrador del técnico solo manda lo respondido. Al retomarlo, lo que no
 * viene en la respuesta del servidor es lo que quedó pendiente.
 */
class ServiceReportDraftTest extends TestCase
{
    use RefreshDatabase;

    private User $technician;

    private Equipment $equipment;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([PermissionSeeder::class, RoleSeeder::class]);

        $this->technician = User::factory()->create();
        $this->technician->assignRole('technician');

        $client = Client::create(['business_name' => 'Edificio Central', 'nit' => '900123456-1']);
        $site = Site::create(['client_id' => $client->id, 'name' => 'Torre A', 'address' => 'Calle 1']);
        $this->equipment = Equipment::create([
            'site_id' => $site->id,
            'internal_code' => 'TZ-A-0001',
            'equipment_type' => 'ascensor',
        ]);

        Sanctum::actingAs($this->technician);
    }

    private function draft(): int
    {
        return $this->postJson('/api/service-reports', [
            'report_type' => 'RSTP',
            'equipment_id' => $this->equipment->id,
            'service_date' => now()->toDateString(),
            'entry_time' => '08:00',
            'status' => 'borrador',
        ])->assertCreated()->json('id');
    }

    private function save(int $id, array $answers): void
    {
        $this->putJson("/api/service-reports/{$id}", array_merge([
            'report_type' => 'RSTP',
            'equipment_id' => $this->equipment->id,
            'service_date' => now()->toDateString(),
        ], $answers))->assertOk();
    }

    public function test_el_borrador_guarda_solo_lo_respondido(): void
    {
        $id = $this->draft();

        $this->save($id, [
            'initial_conditions' => [['condition_key' => 'fuera_de_servicio', 'value' => 'si', 'observation' => null]],
            'rstp_activities' => [['group_key' => 'cabina', 'activity_key' => 'limpieza', 'is_ok' => false, 'observation' => null]],
        ]);

        $report = $this->getJson("/api/service-reports/{$id}")->assertOk()->json();
        $this->assertSame(['fuera_de_servicio'], array_column($report['initial_conditions'], 'condition_key'));
        $this->assertSame('si', $report['initial_conditions'][0]['value']);
        $this->assertSame(['limpieza'], array_column($report['rstp_activities'], 'activity_key'));
        $this->assertFalse((bool) $report['rstp_activities'][0]['is_ok']);
    }

    public function test_quitar_la_ultima_respuesta_deja_el_borrador_vacio(): void
    {
        $id = $this->draft();
        $this->save($id, [
            'initial_conditions' => [['condition_key' => 'fuera_de_servicio', 'value' => 'si', 'observation' => null]],
        ]);

        $this->save($id, ['initial_conditions' => [], 'rstp_activities' => []]);

        $report = $this->getJson("/api/service-reports/{$id}")->assertOk()->json();
        $this->assertSame([], $report['initial_conditions']);
        $this->assertSame([], $report['rstp_activities']);
    }
}
