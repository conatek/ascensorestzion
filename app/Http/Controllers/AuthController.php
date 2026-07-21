<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Credenciales inválidas',
            ], 401);
        }

        if (! $user->active) {
            return response()->json([
                'message' => 'Tu cuenta está desactivada. Contacta al administrador.',
            ], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user->load(['roles:id,name', 'client:id,business_name']),
            'permissions' => $user->getAllPermissions()->pluck('name'),
        ]);
    }

    public function me(Request $request)
    {
        $user = $request->user()->load(['roles:id,name', 'client:id,business_name']);

        return response()->json([
            'user' => $user,
            'permissions' => $user->getAllPermissions()->pluck('name'),
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Sesión cerrada correctamente',
        ]);
    }

    public function logoutAll(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json([
            'message' => 'Todos los tokens revocados',
        ]);
    }

    public function impersonate(Request $request, $clientId)
    {
        $master = $request->user();

        if (! $master->hasRole('master') && ! $master->hasRole('super')) {
            return response()->json(['message' => 'No autorizado.'], 403);
        }

        // Buscar un usuario admin activo del cliente
        $user = User::where('client_id', $clientId)
            ->where('active', true)
            ->role('admin')
            ->first();

        if (! $user) {
            return response()->json(['message' => 'Este cliente no tiene un usuario administrador activo.'], 422);
        }

        $token = $user->createToken('impersonation')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'user' => $user->load(['roles:id,name', 'client:id,business_name']),
            'permissions' => $user->getAllPermissions()->pluck('name'),
            'original_token' => $request->bearerToken(),
            'original_user' => $master->load(['roles:id,name', 'client:id,business_name']),
            'original_permissions' => $master->getAllPermissions()->pluck('name'),
        ]);
    }

    public function stopImpersonating(Request $request)
    {
        // Eliminar el token de impersonation actual
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Impersonacion finalizada.']);
    }
}
