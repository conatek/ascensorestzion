<?php

namespace App\Notifications;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/**
 * Un tecnico intento registrar la llegada a un equipo fuera de su visita. Solo
 * va a la campana: lo urgente es que el master lo vea en el panel y desbloquee.
 */
class EquipmentAccessBlockedNotification extends Notification
{
    use Queueable;

    public function __construct(
        private array $payload,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $tech = $this->payload['technician_name'] ?? 'Un técnico';
        $code = $this->payload['equipment_code'] ?? '';

        $detail = ($this->payload['reason'] ?? null) === 'anticipado' && ! empty($this->payload['scheduled_start'])
            ? 'antes de la hora (visita a las '.Carbon::parse($this->payload['scheduled_start'])->format('g:i A').')'
            : 'sin visita programada hoy';

        return [
            'type' => 'equipment_access_blocked',
            ...$this->payload,
            'message' => "🔒 {$tech} intentó ingresar al equipo {$code} {$detail}",
        ];
    }
}
