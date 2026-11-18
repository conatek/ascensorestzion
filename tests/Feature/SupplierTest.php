<?php

namespace Tests\Feature;

use App\Events\SupplyDataChanged;
use App\Models\Supplier;
use App\Models\SupplierItem;
use App\Models\SupplyItem;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RoleSeeder;
use Database\Seeders\SupplierExampleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Event;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Directorio de proveedores (fase 1): solo master, sedes, asesores, productos
 * con precio fechado e historial, edicion compartida entre varios master y los
 * datos de ejemplo.
 */
class SupplierTest extends TestCase
{
    use RefreshDatabase;

    private User $master;

    private User $otherMaster;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([PermissionSeeder::class, RoleSeeder::class]);
        Event::fake([SupplyDataChanged::class]);

        $this->master = User::factory()->create(['name' => 'Ana Master', 'active' => true]);
        $this->master->assignRole('master');

        $this->otherMaster = User::factory()->create(['name' => 'Beto Master', 'active' => true]);
        $this->otherMaster->assignRole('master');

        Sanctum::actingAs($this->master);
    }

    private function createSupplier(array $data = []): Supplier
    {
        $id = $this->postJson('/api/suppliers', array_merge([
            'business_name' => 'Repuestos del Norte S.A.S.',
            'nit' => '901.111.111-1',
            'supplier_type' => 'distribuidor',
        ], $data))->assertCreated()->json('id');

        return Supplier::findOrFail($id);
    }

    public function test_solo_master_entra_al_modulo(): void
    {
        $supplier = $this->createSupplier();

        foreach (['super', 'coordinator', 'technician', 'admin'] as $role) {
            $user = User::factory()->create(['active' => true]);
            $user->assignRole($role);
            Sanctum::actingAs($user);

            $this->getJson('/api/suppliers')->assertForbidden();
            $this->getJson("/api/suppliers/{$supplier->id}")->assertForbidden();
            $this->postJson('/api/suppliers', ['business_name' => 'X', 'supplier_type' => 'taller'])->assertForbidden();
            $this->getJson('/api/supply-items/search?q=contactor')->assertForbidden();
        }
    }

    public function test_crea_proveedor_con_sede_y_asesor_principal_en_un_paso(): void
    {
        $supplier = $this->createSupplier([
            'categories' => ['Tarjetas electrónicas'],
            'main_branch' => ['city' => 'Bogotá', 'address' => 'Cra. 1 # 2-3', 'phone' => '601 555 0000'],
            'primary_contact' => ['name' => 'Laura Rincón', 'mobile' => '300 555 0000', 'email' => 'laura@correo.com'],
        ]);

        $data = $this->getJson("/api/suppliers/{$supplier->id}")->assertOk()->json();

        $this->assertSame('Principal', $data['branches'][0]['name']);
        $this->assertTrue($data['branches'][0]['is_main']);
        $this->assertSame('Laura Rincón', $data['contacts'][0]['name']);
        $this->assertTrue($data['contacts'][0]['is_primary']);
        $this->assertSame('300 555 0000', $data['contacts'][0]['whatsapp']);
        $this->assertSame('Ana Master', $data['creator']['name']);
    }

    public function test_el_nit_no_se_repite_pero_puede_ir_vacio(): void
    {
        $this->createSupplier();

        $this->postJson('/api/suppliers', [
            'business_name' => 'Otro', 'nit' => '901.111.111-1', 'supplier_type' => 'taller',
        ])->assertStatus(422)->assertJsonValidationErrors('nit');

        // Borrar un proveedor libera su NIT: se puede volver a registrar.
        $deleted = $this->createSupplier(['business_name' => 'Se va', 'nit' => '903.333.333-3']);
        $this->deleteJson("/api/suppliers/{$deleted->id}")->assertNoContent();
        $this->createSupplier(['business_name' => 'Vuelve', 'nit' => '903.333.333-3']);

        $this->postJson('/api/suppliers', ['business_name' => 'Sin NIT 1', 'nit' => '', 'supplier_type' => 'taller'])->assertCreated();
        $this->postJson('/api/suppliers', ['business_name' => 'Sin NIT 2', 'nit' => '', 'supplier_type' => 'taller'])->assertCreated();
    }

    public function test_sedes_una_sola_principal_y_hereda_al_borrar(): void
    {
        $supplier = $this->createSupplier();

        $a = $this->postJson("/api/suppliers/{$supplier->id}/branches", ['name' => 'Bogotá', 'city' => 'Bogotá'])
            ->assertCreated()->json();
        $this->assertTrue($a['is_main'], 'La primera sede queda como principal');

        $b = $this->postJson("/api/suppliers/{$supplier->id}/branches", ['name' => 'Cali', 'city' => 'Cali', 'is_main' => true])
            ->assertCreated()->json();

        $this->assertFalse($supplier->branches()->find($a['id'])->is_main);
        $this->assertTrue($supplier->branches()->find($b['id'])->is_main);

        $this->deleteJson("/api/suppliers/{$supplier->id}/branches/{$b['id']}")->assertNoContent();
        $this->assertTrue($supplier->branches()->find($a['id'])->is_main);
    }

    public function test_asesores_un_solo_principal_y_sede_del_mismo_proveedor(): void
    {
        $supplier = $this->createSupplier();
        $other = $this->createSupplier(['business_name' => 'Otro', 'nit' => '902.222.222-2']);
        $foreignBranch = $other->branches()->create(['name' => 'Ajena']);

        $first = $this->postJson("/api/suppliers/{$supplier->id}/contacts", ['name' => 'Uno'])->assertCreated()->json();
        $this->assertTrue($first['is_primary']);

        $second = $this->postJson("/api/suppliers/{$supplier->id}/contacts", ['name' => 'Dos', 'is_primary' => true])
            ->assertCreated()->json();
        $this->assertFalse($supplier->contacts()->find($first['id'])->is_primary);
        $this->assertTrue($supplier->contacts()->find($second['id'])->is_primary);

        $this->postJson("/api/suppliers/{$supplier->id}/contacts", [
            'name' => 'Tres', 'supplier_branch_id' => $foreignBranch->id,
        ])->assertStatus(422)->assertJsonValidationErrors('supplier_branch_id');

        // No se puede tocar el asesor de otro proveedor colandose por la URL.
        $this->putJson("/api/suppliers/{$other->id}/contacts/{$first['id']}", ['name' => 'Hack'])->assertNotFound();
    }

    public function test_producto_nuevo_con_precio_genera_codigo_e_historial(): void
    {
        $supplier = $this->createSupplier();

        $offer = $this->postJson("/api/suppliers/{$supplier->id}/offers", [
            'item' => ['name' => 'Contactor 220 V', 'item_type' => 'repuesto', 'unit' => 'und', 'manufacturer_ref' => 'LC1-D32'],
            'supplier_ref' => 'RN-001',
            'price' => 180000,
            'currency' => 'COP',
            'price_date' => '2026-11-10',
            'lead_time_days' => 3,
        ])->assertCreated()->json();

        $this->assertSame('REP-'.sprintf('%05d', $offer['supply_item']['id']), $offer['supply_item']['code']);
        $this->assertSame('2026-11-10', $offer['price_date']);
        $this->assertEquals(19, $offer['supply_item']['tax_rate']);

        // Cambiar el precio deja la fila anterior en el historial, con quien lo cambio.
        $this->putJson("/api/suppliers/{$supplier->id}/offers/{$offer['id']}", [
            'price' => 195000, 'currency' => 'COP', 'price_date' => '2026-11-18',
        ])->assertOk()->assertJsonPath('price', '195000.00');

        // Cambiar solo una nota no escribe historial.
        $this->putJson("/api/suppliers/{$supplier->id}/offers/{$offer['id']}", [
            'price' => 195000, 'currency' => 'COP', 'price_date' => '2026-11-18', 'notes' => 'Confirmado',
        ])->assertOk();

        $history = $this->getJson("/api/suppliers/{$supplier->id}/offers/{$offer['id']}/history")->assertOk()->json();
        $this->assertCount(2, $history);
        $this->assertSame('195000.00', $history[0]['price']);
        $this->assertSame('180000.00', $history[1]['price']);
        $this->assertSame('Ana Master', $history[0]['user']['name']);
    }

    public function test_precio_con_iva_incluido_se_guarda_sin_iva(): void
    {
        $supplier = $this->createSupplier();

        $this->postJson("/api/suppliers/{$supplier->id}/offers", [
            'item' => ['name' => 'Pulsador'],
            'price' => 119000,
            'price_includes_tax' => true,
            'currency' => 'COP',
            'price_date' => '2026-11-10',
        ])->assertCreated()->assertJsonPath('price', '100000.00');
    }

    public function test_la_unidad_es_libre_se_normaliza_y_queda_disponible_para_todos(): void
    {
        $supplier = $this->createSupplier();
        $offer = fn (string $name, string $unit) => $this->postJson("/api/suppliers/{$supplier->id}/offers", [
            'item' => ['name' => $name, 'unit' => $unit],
            'price' => 1000, 'currency' => 'COP', 'price_date' => '2026-11-10',
        ])->assertCreated()->json();

        $this->assertSame('caja x 12', $offer('Fusibles', '  Caja  X 12 ')['supply_item']['unit']);
        $offer('Tornillos', 'CAJA x 12');
        $offer('Cable', 'm');
        $this->assertSame('und', $offer('Sin unidad', '   ')['supply_item']['unit']);

        $units = $this->getJson('/api/supply-items/units')->assertOk()->json();
        $this->assertSame('caja x 12', $units[0]);
        $this->assertEqualsCanonicalizing(['caja x 12', 'm', 'und'], $units);
    }

    public function test_un_producto_existente_no_se_duplica_y_admite_dos_proveedores(): void
    {
        $a = $this->createSupplier();
        $b = $this->createSupplier(['business_name' => 'Importadora B', 'nit' => '902.222.222-2']);

        $offer = $this->postJson("/api/suppliers/{$a->id}/offers", [
            'item' => ['name' => 'Cable de tracción 10 mm', 'unit' => 'm'],
            'price' => 15000, 'currency' => 'COP', 'price_date' => '2026-11-10', 'is_preferred' => true,
        ])->assertCreated()->json();
        $itemId = $offer['supply_item']['id'];

        $found = $this->getJson('/api/supply-items/search?q=tracci')->assertOk()->json();
        $this->assertCount(1, $found);
        $this->assertSame(1, $found[0]['offers_count']);

        $second = $this->postJson("/api/suppliers/{$b->id}/offers", [
            'supply_item_id' => $itemId, 'price' => 9.5, 'currency' => 'USD', 'price_date' => '2026-11-12', 'is_preferred' => true,
        ])->assertCreated()->json();

        $this->assertSame(1, SupplyItem::count());
        // El preferido es uno solo por producto.
        $this->assertFalse(SupplierItem::find($offer['id'])->is_preferred);
        $this->assertTrue(SupplierItem::find($second['id'])->is_preferred);

        // El mismo proveedor no puede tener dos precios para el mismo producto.
        $this->postJson("/api/suppliers/{$b->id}/offers", [
            'supply_item_id' => $itemId, 'price' => 10, 'currency' => 'USD', 'price_date' => '2026-11-12',
        ])->assertStatus(422)->assertJsonValidationErrors('supply_item_id');
    }

    public function test_precio_viejo_se_marca_como_desactualizado(): void
    {
        $supplier = $this->createSupplier();

        $old = $this->postJson("/api/suppliers/{$supplier->id}/offers", [
            'item' => ['name' => 'Viejo'], 'price' => 1000, 'currency' => 'COP',
            'price_date' => now()->subDays(120)->toDateString(),
        ])->assertCreated()->json();

        $recent = $this->postJson("/api/suppliers/{$supplier->id}/offers", [
            'item' => ['name' => 'Reciente'], 'price' => 1000, 'currency' => 'COP',
            'price_date' => now()->subDays(5)->toDateString(),
        ])->assertCreated()->json();

        $this->assertTrue($old['is_stale']);
        $this->assertFalse($recent['is_stale']);
    }

    public function test_el_directorio_busca_por_nombre_ciudad_asesor_y_por_lo_que_vende(): void
    {
        $a = $this->createSupplier([
            'main_branch' => ['city' => 'Medellín'],
            'primary_contact' => ['name' => 'Paola Mejía'],
        ]);
        $this->createSupplier(['business_name' => 'Taller El Rotor', 'nit' => '902.222.222-2', 'supplier_type' => 'taller']);

        $this->postJson("/api/suppliers/{$a->id}/offers", [
            'item' => ['name' => 'Fotocelda de puerta', 'manufacturer_ref' => 'CL-917'],
            'price' => 600000, 'currency' => 'COP', 'price_date' => '2026-11-10',
        ])->assertCreated();

        foreach (['Norte', '901.111', 'Medell', 'Paola', 'fotocelda', 'CL-917'] as $term) {
            $rows = $this->getJson('/api/suppliers?search='.urlencode($term))->assertOk()->json();
            $this->assertCount(1, $rows, "Buscar '{$term}'");
            $this->assertSame($a->id, $rows[0]['id']);
        }

        $rows = $this->getJson('/api/suppliers?type=taller')->assertOk()->json();
        $this->assertCount(1, $rows);
        $this->assertSame('Taller El Rotor', $rows[0]['business_name']);

        $all = $this->getJson('/api/suppliers')->assertOk()->json();
        $this->assertCount(2, $all);
        $first = collect($all)->firstWhere('id', $a->id);
        $this->assertSame('Medellín', $first['main_branch']['city']);
        $this->assertSame('Paola Mejía', $first['primary_contact']['name']);
        $this->assertSame(1, $first['items_count']);
    }

    public function test_cualquier_master_edita_lo_de_otro_y_queda_la_autoria(): void
    {
        $supplier = $this->createSupplier();

        Sanctum::actingAs($this->otherMaster);
        $this->putJson("/api/suppliers/{$supplier->id}", [
            'business_name' => 'Repuestos del Norte', 'supplier_type' => 'distribuidor', 'nit' => '901.111.111-1',
        ])->assertOk();

        $data = $this->getJson("/api/suppliers/{$supplier->id}")->json();
        $this->assertSame('Ana Master', $data['creator']['name']);
        $this->assertSame('Beto Master', $data['editor']['name']);
    }

    public function test_guardar_sobre_una_version_vieja_devuelve_409(): void
    {
        $supplier = $this->createSupplier();
        $loadedAt = $supplier->updated_at->toIso8601String();

        // Beto guarda primero...
        $this->travel(5)->seconds();
        Sanctum::actingAs($this->otherMaster);
        $this->putJson("/api/suppliers/{$supplier->id}", [
            'business_name' => 'Cambio de Beto', 'supplier_type' => 'distribuidor',
            'expected_updated_at' => $loadedAt,
        ])->assertOk();

        // ...y Ana, que tenia la ficha abierta desde antes, no lo pisa.
        Sanctum::actingAs($this->master);
        $this->putJson("/api/suppliers/{$supplier->id}", [
            'business_name' => 'Cambio de Ana', 'supplier_type' => 'distribuidor',
            'expected_updated_at' => $loadedAt,
        ])->assertStatus(409)->assertJsonPath('updated_by', 'Beto Master');

        $this->assertSame('Cambio de Beto', $supplier->fresh()->business_name);
    }

    public function test_cada_cambio_avisa_a_los_demas_master(): void
    {
        $supplier = $this->createSupplier();

        Event::assertDispatched(SupplyDataChanged::class, function (SupplyDataChanged $e) use ($supplier) {
            return $e->entity === 'supplier' && $e->action === 'created'
                && $e->supplierId === $supplier->id && $e->userId === $this->master->id
                && $e->broadcastOn()[0]->name === 'private-masters';
        });
    }

    public function test_el_canal_masters_es_solo_para_master(): void
    {
        $authorize = Broadcast::driver()->getChannels()->get('masters');
        $this->assertNotNull($authorize, 'El canal masters esta registrado');

        $this->assertTrue($authorize($this->master));

        foreach (['super', 'coordinator', 'technician', 'admin'] as $role) {
            $user = User::factory()->create(['active' => true]);
            $user->assignRole($role);
            $this->assertFalse($authorize($user), "El rol {$role} no entra al canal");
        }
    }

    public function test_borrar_un_proveedor_quita_sus_precios_pero_no_los_productos_reales(): void
    {
        $supplier = $this->createSupplier();
        $offer = $this->postJson("/api/suppliers/{$supplier->id}/offers", [
            'item' => ['name' => 'Producto real'], 'price' => 1000, 'currency' => 'COP', 'price_date' => '2026-11-10',
        ])->assertCreated()->json();

        $this->deleteJson("/api/suppliers/{$supplier->id}")->assertNoContent();

        $this->assertSoftDeleted('suppliers', ['id' => $supplier->id]);
        $this->assertDatabaseMissing('supplier_items', ['id' => $offer['id']]);
        $this->assertDatabaseHas('supply_items', ['id' => $offer['supply_item']['id'], 'deleted_at' => null]);
        $this->getJson('/api/suppliers')->assertOk()->assertJsonCount(0);
    }

    public function test_los_ejemplos_se_cargan_una_vez_y_se_van_limpios(): void
    {
        $this->seed(SupplierExampleSeeder::class);
        $this->seed(SupplierExampleSeeder::class); // idempotente

        $this->assertSame(4, Supplier::where('is_example', true)->count());
        $this->assertSame(Supplier::count(), Supplier::where('is_example', true)->count());
        // 15 ofertas sobre 13 productos: 2 productos los venden dos proveedores.
        $this->assertSame(15, SupplierItem::count());
        $this->assertSame(13, SupplyItem::count());
        $this->assertSame(SupplyItem::count(), SupplyItem::where('is_example', true)->count());
        $this->assertTrue(SupplierItem::all()->contains(fn ($o) => $o->is_stale), 'Hay un precio desactualizado de muestra');
        $this->assertTrue(SupplierItem::where('currency', 'USD')->exists());

        foreach (Supplier::all() as $supplier) {
            $this->deleteJson("/api/suppliers/{$supplier->id}")->assertNoContent();
        }

        // Al borrar los proveedores de ejemplo no queda rastro en el catalogo...
        $this->assertSame(0, SupplyItem::withTrashed()->count());
        $this->assertSame(0, SupplierItem::count());

        // ...y volver a correr el seeder no los resucita.
        $this->seed(SupplierExampleSeeder::class);
        $this->assertSame(0, Supplier::count());
    }
}
