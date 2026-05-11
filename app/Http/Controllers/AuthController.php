<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\User;
use App\Services\SubscriptionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    // Login
    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Credenciales inválidas'
            ], 401);
        }

        // Crear token
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type'   => 'Bearer',
            'user'         => $user->load('roles:id,name'),
            'permissions'  => $user->getAllPermissions()->pluck('name'),
        ]);
    }

    // Obtener usuario autenticado
    public function me(Request $request)
    {
        $user = $request->user()->load('roles:id,name');

        return response()->json([
            'user'        => $user,
            'permissions' => $user->getAllPermissions()->pluck('name'),
        ]);
    }

    // Logout (revocar token actual)
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Sesión cerrada correctamente'
        ]);
    }

    // Logout de todos los dispositivos
    public function logoutAll(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json([
            'message' => 'Todos los tokens revocados'
        ]);
    }

    // Registro de usuario
    public function register(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);

        // Crear empresa vacía para el nuevo usuario
        $baseSlug = Str::slug($request->name);
        $slug = $baseSlug;
        $counter = 1;
        while (Company::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $counter++;
        }

        $company = Company::create([
            'name' => 'Empresa de ' . $request->name,
            'slug' => $slug,
        ]);

        $user = User::create([
            'company_id' => $company->id,
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => bcrypt($request->password),
        ]);

        // Asignar el usuario como owner de la empresa
        $company->update(['user_id' => $user->id]);

        $user->assignRole('Guest');

        // Crear suscripción trial
        $subscriptionService = app(SubscriptionService::class);
        $subscriptionService->createTrialSubscription($company);

        // Crear token
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type'   => 'Bearer',
            'user'         => $user->load('roles:id,name'),
            'permissions'  => $user->getAllPermissions()->pluck('name'),
        ], 201);
    }


}
