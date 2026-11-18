<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\SharedEditing;
use App\Http\Requests\SupplierRequest;
use App\Models\Supplier;
use App\Models\SupplyItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

/**
 * Directorio de proveedores. Solo master (middleware de ruta): todos los master
 * ven y editan todo, sin dueño por registro.
 */
class SupplierController extends Controller
{
    use SharedEditing;

    public function index(Request $request): JsonResponse
    {
        $query = Supplier::query()
            ->with(['mainBranch', 'primaryContact'])
            ->withCount(['branches', 'contacts', 'items']);

        if ($request->filled('search')) {
            $s = '%'.trim($request->search).'%';

            // Busca tambien por lo que vende: "¿quien tiene contactores?"
            $query->where(function ($q) use ($s) {
                $q->where('business_name', 'like', $s)
                    ->orWhere('trade_name', 'like', $s)
                    ->orWhere('nit', 'like', $s)
                    ->orWhere('categories', 'like', $s)
                    ->orWhere('brands', 'like', $s)
                    ->orWhereHas('contacts', fn ($c) => $c->where('name', 'like', $s))
                    ->orWhereHas('branches', fn ($b) => $b->where('city', 'like', $s))
                    ->orWhereHas('items', function ($i) use ($s) {
                        $i->where('supplier_ref', 'like', $s)
                            ->orWhereHas('supplyItem', function ($si) use ($s) {
                                $si->where('name', 'like', $s)
                                    ->orWhere('code', 'like', $s)
                                    ->orWhere('manufacturer_ref', 'like', $s);
                            });
                    });
            });
        }

        if ($request->filled('type')) {
            $query->where('supplier_type', $request->type);
        }

        if ($request->filled('city')) {
            $query->whereHas('branches', fn ($b) => $b->where('city', $request->city));
        }

        if ($request->filled('active')) {
            $query->where('active', $request->boolean('active'));
        }

        return response()->json($query->orderBy('business_name')->get());
    }

    public function store(SupplierRequest $request): JsonResponse
    {
        $data = $request->validated();

        $supplier = DB::transaction(function () use ($data) {
            $supplier = Supplier::create(Arr::except($data, ['main_branch', 'primary_contact']));

            $branch = null;
            $branchData = array_filter($data['main_branch'] ?? [], fn ($v) => filled($v));
            if ($branchData) {
                $branch = $supplier->branches()->create($branchData + [
                    'name' => $branchData['name'] ?? 'Principal',
                    'is_main' => true,
                ]);
            }

            $contactData = array_filter($data['primary_contact'] ?? [], fn ($v) => filled($v));
            if (! empty($contactData['name'])) {
                $supplier->contacts()->create($contactData + [
                    'supplier_branch_id' => $branch?->id,
                    'whatsapp' => $contactData['mobile'] ?? null,
                    'is_primary' => true,
                ]);
            }

            return $supplier;
        });

        $this->announce($request, 'supplier', 'created', $supplier->id);

        return response()->json($supplier->fresh(), 201);
    }

    public function show(Supplier $supplier): JsonResponse
    {
        $supplier->load([
            'branches',
            'contacts.branch:id,name,city',
            'items' => fn ($q) => $q->with(['supplyItem', 'editor:id,name'])->orderByDesc('is_preferred'),
            'creator:id,name',
            'editor:id,name',
        ]);

        return response()->json($supplier);
    }

    public function update(SupplierRequest $request, Supplier $supplier): JsonResponse
    {
        $this->abortIfStale($request, $supplier);

        $supplier->update($request->validated());

        $this->announce($request, 'supplier', 'updated', $supplier->id);

        return response()->json($supplier->fresh());
    }

    public function destroy(Request $request, Supplier $supplier): JsonResponse
    {
        DB::transaction(function () use ($supplier) {
            $itemIds = $supplier->items()->pluck('supply_item_id');

            // Sus ofertas dejan de existir: un proveedor borrado no debe seguir
            // apareciendo como opcion de compra.
            $supplier->items()->delete();
            $supplier->delete();

            // Al borrar un proveedor de ejemplo se van tambien sus productos de
            // ejemplo, si nadie mas los ofrece: el catalogo queda limpio.
            if ($supplier->is_example) {
                SupplyItem::whereIn('id', $itemIds)
                    ->where('is_example', true)
                    ->whereDoesntHave('offers')
                    ->forceDelete();
            }
        });

        $this->announce($request, 'supplier', 'deleted', $supplier->id);

        return response()->json(null, 204);
    }
}
