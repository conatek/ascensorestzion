<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rutas públicas de tarjetas digitales (antes del catch-all)
|--------------------------------------------------------------------------
|
| /{company_slug}              → Vista pública de la empresa
| /{company_slug}/{card_slug}  → Tarjeta individual
|
| En el futuro, si se quieren renderizar desde el servidor:
|
|   Route::get('/{company}', [PublicCardController::class, 'company'])
|       ->where('company', '[a-z0-9\-_]+');
|
|   Route::get('/{company}/{card}', [PublicCardController::class, 'card'])
|       ->where(['company' => '[a-z0-9\-_]+', 'card' => '[a-z0-9\-_]+']);
|
| Por ahora el SPA de Vue maneja estas rutas en el frontend.
| El catch-all sirve la app y Vue Router resuelve internamente.
|
*/

Route::get('/{any}', function () {
    return view('app');
})->where('any', '.*');
