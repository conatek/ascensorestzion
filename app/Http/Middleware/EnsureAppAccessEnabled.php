<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\HttpFoundation\Response;

/**
 * Interruptor global de acceso (APP_ACCESS_ENABLED en .env).
 *
 * Apagado: nadie inicia sesion y se borran TODOS los tokens, asi que al volver
 * a encenderlo cada usuario tiene que entrar de nuevo. Las rutas publicas
 * (tarjetas, confirmacion de reportes, contacto) no pasan por aqui.
 */
class EnsureAppAccessEnabled
{
    public const MESSAGE = 'El acceso a la aplicación está deshabilitado temporalmente.';

    public function handle(Request $request, Closure $next): Response
    {
        if (config('app.access_enabled')) {
            return $next($request);
        }

        self::revokeAllTokens();

        // 401 hace que el interceptor de axios borre la sesion local y lleve al login.
        return response()->json(['message' => self::MESSAGE], 401);
    }

    public static function revokeAllTokens(): void
    {
        PersonalAccessToken::query()->delete();
    }
}
