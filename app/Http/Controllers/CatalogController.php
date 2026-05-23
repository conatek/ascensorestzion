<?php

namespace App\Http\Controllers;

use App\Models\Catalog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CatalogController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Catalog::active()->orderBy('sort_order');

        if ($request->filled('scope')) {
            $query->forScope($request->scope);
        }

        if ($request->filled('category')) {
            $query->forCategory($request->category);
        }

        return response()->json($query->get());
    }

    public function store(Request $request): JsonResponse
    {
        abort_if(!$request->user()->can('manage_catalogs'), 403, 'No autorizado.');

        $data = $request->validate([
            'scope'     => ['required', 'string', 'max:50'],
            'category'  => ['required', 'string', 'max:50'],
            'group_key' => ['nullable', 'string', 'max:50'],
            'key'       => ['required', 'string', 'max:80'],
            'label'     => ['required', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer'],
            'active'    => ['nullable', 'boolean'],
        ]);

        $catalog = Catalog::create($data);

        return response()->json($catalog, 201);
    }

    public function update(Request $request, Catalog $catalog): JsonResponse
    {
        abort_if(!$request->user()->can('manage_catalogs'), 403, 'No autorizado.');

        $data = $request->validate([
            'label'     => ['sometimes', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer'],
            'active'    => ['nullable', 'boolean'],
        ]);

        $catalog->update($data);

        return response()->json($catalog->fresh());
    }

    public function destroy(Request $request, Catalog $catalog): JsonResponse
    {
        abort_if(!$request->user()->can('manage_catalogs'), 403, 'No autorizado.');

        $catalog->delete();

        return response()->json(null, 204);
    }
}
