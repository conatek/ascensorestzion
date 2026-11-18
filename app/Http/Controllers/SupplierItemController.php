<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\SharedEditing;
use App\Models\Supplier;
use App\Models\SupplierItem;
use App\Models\SupplyItem;
use App\Services\SupplierOfferService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Productos y precios de un proveedor: la oferta (precio fechado) que cuelga de
 * un item del catalogo. El item se puede crear aqui mismo.
 */
class SupplierItemController extends Controller
{
    use SharedEditing;

    public function __construct(private SupplierOfferService $offers) {}

    public function store(Request $request, Supplier $supplier): JsonResponse
    {
        $data = $request->validate($this->offerRules() + [
            'supply_item_id' => [
                'nullable',
                'required_without:item',
                Rule::exists('supply_items', 'id')->whereNull('deleted_at'),
                Rule::unique('supplier_items', 'supply_item_id')->where('supplier_id', $supplier->id),
            ],
            'item' => ['nullable', 'required_without:supply_item_id', 'array'],
            'item.name' => ['required_with:item', 'string', 'max:255'],
            'item.item_type' => ['nullable', Rule::in(SupplyItem::TYPES)],
            'item.description' => ['nullable', 'string'],
            'item.category' => ['nullable', 'string', 'max:80'],
            'item.manufacturer_ref' => ['nullable', 'string', 'max:100'],
            'item.compatible_brands' => ['nullable', 'array'],
            'item.compatible_brands.*' => ['string', 'max:60'],
            'item.compatible_models' => ['nullable', 'string', 'max:255'],
            'item.unit' => ['nullable', 'string', 'max:20'],
            'item.tax_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ], $this->messages());

        $newItem = $data['item'] ?? null;
        unset($data['item']);

        if ($newItem) {
            $newItem = array_filter($newItem, fn ($v) => $v !== null && $v !== '');
        }

        $offer = $this->offers->create($supplier, $data, $newItem ?: null, $request->user()->id);

        $this->announce($request, 'offer', 'created', $supplier->id);

        return response()->json($this->present($offer), 201);
    }

    public function update(Request $request, Supplier $supplier, SupplierItem $offer): JsonResponse
    {
        $this->ensureBelongs($supplier, $offer);
        $this->abortIfStale($request, $offer);

        $data = $request->validate($this->offerRules(), $this->messages());

        $offer = $this->offers->update($offer, $data, $request->user()->id);

        $this->announce($request, 'offer', 'updated', $supplier->id);

        return response()->json($this->present($offer));
    }

    public function destroy(Request $request, Supplier $supplier, SupplierItem $offer): JsonResponse
    {
        $this->ensureBelongs($supplier, $offer);

        $item = $offer->supplyItem()->first();
        $offer->delete();

        // Un producto de ejemplo que ya nadie ofrece no pinta nada en el catalogo.
        if ($item && $item->is_example && ! $item->offers()->exists()) {
            $item->forceDelete();
        }

        $this->announce($request, 'offer', 'deleted', $supplier->id);

        return response()->json(null, 204);
    }

    public function history(Supplier $supplier, SupplierItem $offer): JsonResponse
    {
        $this->ensureBelongs($supplier, $offer);

        return response()->json($offer->priceLogs()->with('user:id,name')->get());
    }

    private function offerRules(): array
    {
        return [
            'supplier_ref' => ['nullable', 'string', 'max:100'],
            'price' => ['required', 'numeric', 'min:0', 'max:999999999999'],
            'price_includes_tax' => ['nullable', 'boolean'],
            'currency' => ['required', Rule::in(Supplier::CURRENCIES)],
            'min_quantity' => ['nullable', 'numeric', 'min:0.01'],
            'lead_time_days' => ['nullable', 'integer', 'min:0', 'max:365'],
            'price_date' => ['required', 'date'],
            'valid_until' => ['nullable', 'date'],
            'source' => ['nullable', Rule::in(SupplierItem::SOURCES)],
            'is_preferred' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string'],
        ];
    }

    private function messages(): array
    {
        return [
            'supply_item_id.required_without' => 'Elige un producto o crea uno nuevo.',
            'supply_item_id.unique' => 'Este proveedor ya tiene precio para ese producto: edítalo en la lista.',
            'item.name.required_with' => 'El nombre del producto es obligatorio.',
            'price.required' => 'El precio es obligatorio.',
            'price.numeric' => 'El precio debe ser un número.',
            'currency.required' => 'Elige la moneda.',
            'price_date.required' => 'Indica la fecha en que te dieron el precio.',
        ];
    }

    private function present(SupplierItem $offer): SupplierItem
    {
        return $offer->fresh()->load(['supplyItem', 'editor:id,name']);
    }

    private function ensureBelongs(Supplier $supplier, SupplierItem $offer): void
    {
        abort_if((int) $offer->supplier_id !== (int) $supplier->id, 404);
    }
}
