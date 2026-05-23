<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSiteRequest;
use App\Models\Client;
use App\Models\Site;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SiteController extends Controller
{
    public function index(Request $request, Client $client): JsonResponse
    {
        $user = $request->user();
        abort_if(!$user->can('view_sites'), 403, 'No autorizado.');

        if ($user->hasRole('admin') && $user->client_id !== $client->id) {
            abort(403, 'No autorizado.');
        }

        $sites = $client->sites()
            ->withCount('equipment')
            ->orderBy('name')
            ->get();

        return response()->json($sites);
    }

    public function store(StoreSiteRequest $request, Client $client): JsonResponse
    {
        abort_if(!$request->user()->can('create_site'), 403, 'No autorizado.');

        $data = $request->validated();
        $site = $client->sites()->create($data);

        return response()->json($site, 201);
    }

    public function show(Request $request, Client $client, Site $site): JsonResponse
    {
        $user = $request->user();
        abort_if(!$user->can('view_sites'), 403, 'No autorizado.');

        if ($user->hasRole('admin') && $user->client_id !== $client->id) {
            abort(403, 'No autorizado.');
        }

        $site->load(['equipment' => function ($q) {
            $q->orderBy('internal_code');
        }]);

        return response()->json($site);
    }

    public function update(StoreSiteRequest $request, Client $client, Site $site): JsonResponse
    {
        abort_if(!$request->user()->can('edit_site'), 403, 'No autorizado.');

        $site->update($request->validated());

        return response()->json($site->fresh());
    }

    public function destroy(Request $request, Client $client, Site $site): JsonResponse
    {
        abort_if(!$request->user()->can('delete_site'), 403, 'No autorizado.');

        $site->delete();

        return response()->json(null, 204);
    }
}
