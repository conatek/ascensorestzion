<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

class PasswordResetController extends Controller
{
    /**
     * Envía el enlace de restablecimiento al correo indicado.
     * Responde siempre con éxito para no revelar qué correos existen.
     */
    public function forgot(Request $request): JsonResponse
    {
        $request->validate(['email' => ['required', 'email']]);

        Password::sendResetLink($request->only('email'));

        return response()->json([
            'message' => 'Si el correo está registrado, te enviamos un enlace para restablecer tu contraseña.',
        ]);
    }

    /**
     * Restablece la contraseña con el token recibido por correo.
     */
    public function reset(Request $request): JsonResponse
    {
        $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill(['password' => Hash::make($password)])->save();
                // Revocar tokens existentes por seguridad: la sesión vieja queda invalidada.
                $user->tokens()->delete();
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return response()->json([
                'message' => 'Contraseña restablecida. Ya puedes iniciar sesión.',
            ]);
        }

        $reason = $status === Password::INVALID_USER
            ? 'No encontramos una cuenta con ese correo.'
            : 'El enlace es inválido o expiró. Solicita uno nuevo.';

        return response()->json([
            'message' => $reason,
            'errors' => ['email' => [$reason]],
        ], 422);
    }
}
