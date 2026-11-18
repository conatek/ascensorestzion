<?php

namespace App\Services;

use App\Models\Supplier;
use App\Models\SupplierItem;
use App\Models\SupplierPriceLog;
use App\Models\SupplyItem;
use Illuminate\Support\Facades\DB;

/**
 * Guarda la oferta de un proveedor para un item y deja el historial de precios
 * en la misma transaccion (no con un observer: la importacion masiva de la
 * fase 4 necesita controlarlo).
 */
class SupplierOfferService
{
    /**
     * @param  array  $data  campos de supplier_items; `price` puede venir con IVA
     *                       si `price_includes_tax` es true.
     * @param  array|null  $newItem  datos de un SupplyItem nuevo, si no existe aun.
     */
    public function create(Supplier $supplier, array $data, ?array $newItem, ?int $userId): SupplierItem
    {
        return DB::transaction(function () use ($supplier, $data, $newItem, $userId) {
            if ($newItem) {
                $item = SupplyItem::create($newItem + ['is_example' => $supplier->is_example]);
                $data['supply_item_id'] = $item->id;
            } else {
                $item = SupplyItem::findOrFail($data['supply_item_id']);
            }

            $offer = new SupplierItem($this->normalize($data, $item) + [
                'supplier_id' => $supplier->id,
                'supply_item_id' => $item->id,
            ]);
            $offer->save();

            $this->keepSinglePreferred($offer);
            $this->log($offer, $userId);

            return $offer;
        });
    }

    public function update(SupplierItem $offer, array $data, ?int $userId): SupplierItem
    {
        return DB::transaction(function () use ($offer, $data, $userId) {
            $item = $offer->supplyItem()->first();
            $offer->fill($this->normalize($data, $item));

            $priceChanged = $offer->isDirty(['price', 'currency', 'price_date']);
            $offer->save();

            $this->keepSinglePreferred($offer);

            if ($priceChanged) {
                $this->log($offer, $userId);
            }

            return $offer;
        });
    }

    /** El precio se guarda siempre sin IVA. */
    private function normalize(array $data, SupplyItem $item): array
    {
        if (! empty($data['price_includes_tax']) && isset($data['price'])) {
            $rate = (float) $item->tax_rate;
            $data['price'] = round(((float) $data['price']) / (1 + $rate / 100), 2);
        }

        unset($data['price_includes_tax'], $data['supply_item_id']);

        return $data;
    }

    /** Un solo proveedor preferido por item. */
    private function keepSinglePreferred(SupplierItem $offer): void
    {
        if ($offer->is_preferred) {
            SupplierItem::where('supply_item_id', $offer->supply_item_id)
                ->where('id', '!=', $offer->id)
                ->update(['is_preferred' => false]);
        }
    }

    private function log(SupplierItem $offer, ?int $userId): void
    {
        SupplierPriceLog::create([
            'supplier_item_id' => $offer->id,
            'price' => $offer->price,
            'currency' => $offer->currency,
            'price_date' => $offer->price_date,
            'source' => $offer->source,
            'user_id' => $userId,
        ]);
    }
}
