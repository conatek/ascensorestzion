<?php

namespace App\Channels;

use App\Services\WhatsAppService;
use Illuminate\Notifications\Notification;

class WhatsAppChannel
{
    public function __construct(
        private WhatsAppService $whatsApp,
    ) {}

    public function send(object $notifiable, Notification $notification): void
    {
        if (! $this->whatsApp->isConfigured()) {
            return;
        }

        // Verificar preferencias del usuario
        $prefs = $notifiable->notification_preferences ?? [];
        if (isset($prefs['whatsapp']) && $prefs['whatsapp'] === false) {
            return;
        }

        $phone = $notifiable->routeNotificationForWhatsApp($notification);
        if (! $phone) {
            return;
        }

        $message = $notification->toWhatsApp($notifiable);
        if (! $message) {
            return;
        }

        $this->whatsApp->sendTemplate(
            $phone,
            $message['template'],
            $message['parameters'] ?? [],
            $message['language'] ?? 'es',
        );
    }
}
