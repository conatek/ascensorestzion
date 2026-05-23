<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserAdminController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = User::with(['company:id,name', 'client:id,business_name', 'roles:id,name']);

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

        $users = $query->latest()->get();

        return response()->json($users);
    }

    public function show(User $user): JsonResponse
    {
        $user->load(['company:id,name,slug', 'client:id,business_name', 'roles:id,name']);

        return response()->json($user);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'            => 'required|string|max:255',
            'email'           => 'required|email|unique:users',
            'password'        => 'required|string|min:8',
            'role'            => 'required|in:master,coordinator,technician,admin',
            'phone'           => 'nullable|string|max:20',
            'document_type'   => 'nullable|in:CC,CE,NIT,PP',
            'document_number' => 'nullable|string|max:30',
            'company_id'      => 'nullable|exists:companies,id',
            'client_id'       => 'nullable|exists:clients,id',
        ]);

        $user = User::create([
            'name'            => $data['name'],
            'email'           => $data['email'],
            'password'        => Hash::make($data['password']),
            'phone'           => $data['phone'] ?? null,
            'document_type'   => $data['document_type'] ?? null,
            'document_number' => $data['document_number'] ?? null,
            'company_id'      => $data['company_id'] ?? null,
            'client_id'       => $data['client_id'] ?? null,
            'active'          => true,
        ]);

        $user->assignRole($data['role']);

        return response()->json($user->load(['roles:id,name', 'client:id,business_name']), 201);
    }

    public function update(Request $request, User $user): JsonResponse
    {
        $data = $request->validate([
            'name'            => 'sometimes|string|max:255',
            'email'           => 'sometimes|email|unique:users,email,' . $user->id,
            'role'            => 'sometimes|string|in:master,coordinator,technician,admin',
            'phone'           => 'nullable|string|max:20',
            'document_type'   => 'nullable|in:CC,CE,NIT,PP',
            'document_number' => 'nullable|string|max:30',
            'company_id'      => 'sometimes|nullable|exists:companies,id',
            'client_id'       => 'sometimes|nullable|exists:clients,id',
            'active'          => 'sometimes|boolean',
        ]);

        if (isset($data['role'])) {
            $user->syncRoles([$data['role']]);
            unset($data['role']);
        }

        $user->update($data);

        return response()->json($user->fresh()->load(['company:id,name', 'client:id,business_name', 'roles:id,name']));
    }
}
