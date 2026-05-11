<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class UserAdminController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = User::with(['company:id,name', 'roles:id,name']);

        if ($request->filled('role')) {
            $query->role($request->role);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $perPage = min($request->integer('per_page', 20), 1000);
        $users = $query->latest()->paginate($perPage);

        return response()->json($users);
    }

    public function show(User $user): JsonResponse
    {
        $user->load(['company:id,name,slug', 'roles:id,name']);

        return response()->json($user);
    }

    public function update(Request $request, User $user): JsonResponse
    {
        $data = $request->validate([
            'role'       => 'sometimes|string|in:Master,Admin,Guest',
            'company_id' => 'sometimes|nullable|exists:companies,id',
        ]);

        if (isset($data['role'])) {
            $user->syncRoles([$data['role']]);
        }

        if (array_key_exists('company_id', $data)) {
            $user->update(['company_id' => $data['company_id']]);
        }

        return response()->json($user->fresh()->load(['company:id,name', 'roles:id,name']));
    }
}
