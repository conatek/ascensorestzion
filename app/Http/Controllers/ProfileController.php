<?php

namespace App\Http\Controllers;

use App\Services\CloudinaryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function __construct(
        private CloudinaryService $cloudinary,
    ) {}

    /**
     * Devuelve el usuario autenticado con sus relaciones y permisos.
     */
    private function userResponse($user): JsonResponse
    {
        return response()->json([
            'user' => $user->fresh()->load(['roles:id,name', 'client:id,business_name']),
            'permissions' => $user->getAllPermissions()->pluck('name'),
        ]);
    }

    /**
     * Actualiza los datos de la cuenta del usuario autenticado.
     * Disponible para todos los roles (gestión de su propio perfil).
     */
    public function update(Request $request): JsonResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:20'],
            'document_type' => ['nullable', 'in:CC,CE,NIT,PP'],
            'document_number' => ['nullable', 'string', 'max:30'],
        ]);

        $user->update($data);

        return response()->json([
            'user' => $user->fresh()->load(['roles:id,name', 'client:id,business_name']),
            'permissions' => $user->getAllPermissions()->pluck('name'),
        ]);
    }

    /**
     * Cambia la contraseña del usuario autenticado verificando la actual.
     */
    public function updatePassword(Request $request): JsonResponse
    {
        $user = $request->user();

        $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        if (! Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'message' => 'La contraseña actual es incorrecta.',
                'errors' => ['current_password' => ['La contraseña actual es incorrecta.']],
            ], 422);
        }

        $user->update(['password' => Hash::make($request->password)]);

        return response()->json(['message' => 'Contraseña actualizada correctamente.']);
    }

    /**
     * Sube/actualiza el avatar (recortado 1:1 en el cliente) a Cloudinary.
     */
    public function updateAvatar(Request $request): JsonResponse
    {
        $user = $request->user();

        $request->validate([
            'file' => ['required', 'image', 'mimes:jpeg,jpg,png,webp', 'max:5120'], // 5MB
        ]);

        // Borrar el avatar anterior de Cloudinary (si existe) para no acumular.
        if ($user->image_public_id) {
            try {
                $this->cloudinary->destroy($user->image_public_id);
            } catch (\Throwable $e) {
            }
        }

        $folder = CloudinaryService::envFolder().'/usuarios/avatars';
        $uploaded = $this->cloudinary->upload($request->file('file'), $folder);

        $user->update([
            'image_url' => $uploaded['url'],
            'image_public_id' => $uploaded['public_id'],
        ]);

        return $this->userResponse($user);
    }

    /**
     * Elimina el avatar del usuario (Cloudinary + campos).
     */
    public function deleteAvatar(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->image_public_id) {
            try {
                $this->cloudinary->destroy($user->image_public_id);
            } catch (\Throwable $e) {
            }
        }

        $user->update(['image_url' => null, 'image_public_id' => null]);

        return $this->userResponse($user);
    }
}
