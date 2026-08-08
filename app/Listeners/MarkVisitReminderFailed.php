<?php

namespace App\Listeners;

use App\Models\VisitReminder;
use App\Notifications\VisitReminderNotification;
use Illuminate\Notifications\Events\NotificationFailed;

/**
 * Corrige el estado de un recordatorio cuya entrega fallo de verdad.
 *
 * El comando marca "enviado" cuando despacha, pero las notificaciones van por
 * cola: el correo sale minutos despues, en el worker. Sin esto el tablero
 * enseñaria un ✅ junto a un aviso que nunca llego.
 */
class MarkVisitReminderFailed
{
    public function handle(NotificationFailed $event): void
    {
        if (! $event->notification instanceof VisitReminderNotification) {
            return;
        }

        $reminderId = $event->notification->reminderId();

        if (! $reminderId) {
            return;
        }

        VisitReminder::query()
            ->whereKey($reminderId)
            ->update([
                'status' => 'fallido',
                'error' => mb_substr(
                    $event->data['message'] ?? 'La entrega falló en la cola.',
                    0,
                    255
                ),
            ]);
    }
}
