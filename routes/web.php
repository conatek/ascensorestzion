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

            // Transformar la URL de Cloudinary: contenido de 140x140 centrado en un
            // lienzo blanco de 200x200 (margen del 15% por lado).
            //
            // Dos cosas que decide esta transformacion:
            //
            // 1. El TAMAÑO elige el formato de la vista previa en WhatsApp. Con una
            //    imagen grande (1200x630) muestra la tarjeta alta: foto arriba y
            //    textos debajo. Con una imagen pequeña y cuadrada usa el formato
            //    horizontal (miniatura a la izquierda), que es el que queremos.
            //    Con 300x300 el resultado era inconsistente (una tarjeta salia
            //    horizontal y otra alta, con imagenes equivalentes): ese tamaño
            //    esta justo en el umbral. 200x200 queda claramente por debajo.
            //
            // 2. El MARGEN evita que recorte contenido. Aunque la miniatura es
            //    cuadrada como la imagen, WhatsApp la muestra con un zoom que se
            //    come ~10% por lado: sin margen desaparecian el marco de la foto,
            //    la parte superior de la cabeza y la franja inferior del logo.
            //    Con el 15% de blanco alrededor el recorte muerde margen.
            //
            // OJO con el modo del segundo paso: tiene que ser c_lpad y NO c_pad.
            // c_pad escala la imagen para LLENAR el lienzo y despues rellena, asi
            // que volvia a ampliar los 140x140 hasta 200x200 y el margen del primer
            // paso desaparecia (el resultado era identico a un simple w_200,h_200).
            // c_lpad ("limit pad") solo reduce, nunca amplia: deja los 140x140 tal
            // cual y rellena de blanco los 30px de cada lado.
            //
            // Si hiciera falta afinar: subir w/h del primer paso agranda la foto y
            // reduce el margen; bajarlos hacen lo contrario. Subir el lienzo se
            // acerca al umbral y arriesga que vuelva la tarjeta alta.
            if ($image) {
                $image = str_replace(
                    '/image/upload/',
                    '/image/upload/w_140,h_140,c_fit/w_200,h_200,c_lpad,b_white/',
                    $image
                );
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
