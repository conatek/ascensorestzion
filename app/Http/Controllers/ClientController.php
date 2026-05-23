<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreClientRequest;
use App\Http\Requests\UpdateClientRequest;
use App\Models\Client;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_if(!$user->can('view_clients'), 403, 'No autorizado.');

        $query = Client::withCount(['sites', 'equipment']);

        // admin (externo): solo su cliente
        if ($user->hasRole('admin') && $user->client_id) {
            $query->where('id', $user->client_id);
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('business_name', 'like', "%{$s}%")
                  ->orWhere('nit', 'like', "%{$s}%")
                  ->orWhere('contact_name', 'like', "%{$s}%");
            });
        }

        if ($request->filled('active')) {
            $query->where('active', $request->boolean('active'));
        }

        $clients = $query->orderBy('business_name')->get();

        return response()->json($clients);
    }

    public function store(StoreClientRequest $request): JsonResponse
    {
        abort_if(!$request->user()->can('create_client'), 403, 'No autorizado.');

        $client = Client::create($request->validated());

        return response()->json($client, 201);
    }

    public function show(Request $request, Client $client): JsonResponse
    {
        $user = $request->user();
        abort_if(!$user->can('view_clients'), 403, 'No autorizado.');

        if ($user->hasRole('admin') && $user->client_id !== $client->id) {
            abort(403, 'No autorizado.');
        }

        $client->load(['sites' => function ($q) {
            $q->withCount('equipment')->orderBy('name');
        }]);
        $client->loadCount(['equipment']);

        return response()->json($client);
    }

    public function update(UpdateClientRequest $request, Client $client): JsonResponse
    {
        abort_if(!$request->user()->can('edit_client'), 403, 'No autorizado.');

        $client->update($request->validated());

        return response()->json($client->fresh());
    }

    public function destroy(Request $request, Client $client): JsonResponse
    {
        abort_if(!$request->user()->can('delete_client'), 403, 'No autorizado.');

        $client->delete();

        return response()->json(null, 204);
    }
}
