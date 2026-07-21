<?php

use App\Models\Card;
use App\Models\Company;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Tarjeta pública con Open Graph meta tags
|--------------------------------------------------------------------------
|
| Busca una Card activa por slug para inyectar OG tags en el HTML.
| Si no existe, cae al catch-all y Vue Router resuelve internamente.
|
*/

Route::get('/{cardSlug}', function (string $cardSlug) {
    $company = Company::first();

    if ($company) {
        $card = $company->cards()
            ->where('slug', $cardSlug)
            ->where('is_active', true)
            ->first();

        if ($card) {
            $image = $card->thumbnail_path ?? $card->photo_path;

            // Transformar URL de Cloudinary a 300x300 con padding.
            //
            // El tamaño decide el formato de la vista previa en WhatsApp: con una
            // imagen grande (1200x630) muestra la tarjeta alta, con la foto arriba
            // y el texto debajo. Con una imagen pequeña y cuadrada vuelve al
            // formato horizontal (miniatura a la izquierda, textos a la derecha),
            // que es el que queremos. 300x300 es el tamaño nativo del thumbnail.
            //
            // c_pad en vez de c_fill para que una foto no cuadrada (el fallback a
            // photo_path) se rellene en vez de recortarse.
            if ($image) {
                $image = str_replace('/image/upload/', '/image/upload/w_300,h_300,c_pad,b_white/', $image);
            }

            return view('app', [
                'ogTitle' => $card->full_name,
                'ogDescription' => trim(($card->job_title ? $card->job_title.' — ' : '').$company->name),
                'ogImage' => $image,
                'ogUrl' => url($card->slug),
            ]);
        }
    }

    return view('app');
})->where('cardSlug', '[a-z0-9\-_]+');

// Catch-all: sirve la SPA para todas las demas rutas
Route::get('/{any}', function () {
    return view('app');
})->where('any', '.*');
