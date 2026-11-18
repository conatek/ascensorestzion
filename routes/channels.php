<?php

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Datos compartidos entre los master (proveedores, repuestos, precios): quien
// tenga la pantalla abierta la refresca cuando otro master cambia algo.
Broadcast::channel('masters', function ($user) {
    return $user->hasRole('master');
});
