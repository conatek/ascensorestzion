<?php

namespace App\Http\Controllers;

use App\Models\SupplyItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Catalogo de repuestos y servicios. En la fase 1 solo el buscador, que usa el
 * alta de productos de un proveedor para no duplicar items; la gestion completa
 * del catalogo llega en la fase 2.
 */
class SupplyItemController extends Controller
{
    public function search(Request $request): JsonResponse
    {
        $q = trim((string) $request->input('q'));

        if (mb_strlen($q) < 2) {
            return response()->json([]);
        }

        $like = '%'.$q.'%';

        $items = SupplyItem::query()
            ->where('active', true)
            ->where(function ($w) use ($like) {
                $w->where('name', 'like', $like)
                    ->orWhere('code', 'like', $like)
                    ->orWhere('manufacturer_ref', 'like', $like)
                    ->orWhereHas('offers', fn ($o) => $o->where('supplier_ref', 'like', $like));
            })
            ->withCount('offers')
            ->orderBy('name')
            ->limit(15)
            ->get();

        return response()->json($items);
    }

    /**
     * Unidades que ya usa el catalogo, de la mas usada a la menos. El formulario
     * las ofrece antes de dejar escribir una nueva, para no acabar con variantes.
     */
    public function units(): JsonResponse
    {
        $units = SupplyItem::query()
            ->selectRaw('unit, COUNT(*) as uses')
            ->whereNotNull('unit')
            ->groupBy('unit')
            ->orderByDesc('uses')
            ->orderBy('unit')
            ->pluck('unit');

        return response()->json($units);
    }
}
