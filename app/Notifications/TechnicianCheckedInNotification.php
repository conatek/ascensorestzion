<?php

namespace App\Notifications;

use App\Channels\WhatsAppChannel;
use App\Models\TechnicianCheckin;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TechnicianCheckedInNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private TechnicianCheckin $checkin,
    ) {}

    public function via(object $notifiable): array
    {
        $channels = ['database', 'mail'];

        if ($notifiable->phone) {
            $channels[] = WhatsAppChannel::class;
        }

        return $channels;
    }

    public function toMail(object $notifiable): MailMessage
    {
        $checkin = $this->checkin->fresh([
            'equipment.site.client',
            'technician',
        ]);

        $techName = $checkin->technician?->name ?? 'Técnico';
        $equipCode = $checkin->equipment?->internal_code ?? '';
        $equipBrand = trim(($checkin->equipment?->brand ?? '').' '.($checkin->equipment?->model ?? ''));
        $clientName = $checkin->equipment?->site?->client?->business_name ?? '—';
        $siteName = $checkin->equipment?->site?->name ?? '—';

        $subject = $checkin->is_emergency
            ? "⚠ EMERGENCIA — Check-in de {$techName}"
            : "Check-in — {$techName} en {$equipCode}";

        $mail = (new MailMessage)
            ->subject($subject)
            ->greeting('Check-in registrado')
            ->line("**Técnico:** {$techName}")
            ->line("**Equipo:** {$equipCode} ({$equipBrand})")
            ->line("**Cliente:** {$clientName}")
            ->line("**Sede:** {$siteName}")
            ->line("**Hora:** {$checkin->checked_in_at->format('d/m/Y h:i A')}")
            ->salutation('Equipo de Ascensores Tzion');

        if ($checkin->is_emergency && $checkin->response_time_minutes !== null) {
            $mail->line("**Tiempo de respuesta:** {$checkin->response_time_minutes} minutos");
        }

        if ($checkin->needs_review) {
            $mail->line('**⚠ Por revisar:** '.$this->reviewReason($checkin));
        }

        return $mail;
    }

    public function toArray(object $notifiable): array
    {
        $checkin = $this->checkin->fresh([
            'equipment.site.client',
            'technician',
        ]);

        $techName = $checkin->technician?->name ?? 'Técnico';
        $equipCode = $checkin->equipment?->internal_code ?? '';
        $clientName = $checkin->equipment?->site?->client?->business_name ?? '—';
        $siteName = $checkin->equipment?->site?->name ?? '—';

        return [
            'type' => 'technician_checked_in',
            'checkin_id' => $checkin->id,
            'technician_name' => $techName,
            'equipment_code' => $equipCode,
            'client_name' => $clientName,
            'site_name' => $siteName,
            'checked_in_at' => $checkin->checked_in_at->toIso8601String(),
            'is_emergency' => $checkin->is_emergency,
            'response_time_minutes' => $checkin->response_time_minutes,
            'needs_review' => $checkin->needs_review,
            'access_status' => $checkin->access_status,
            'message' => match (true) {
                $checkin->needs_review => "⚠ Por revisar: {$techName} llegó al equipo {$equipCode} sin conexión — ".$this->reviewReason($checkin),
                $checkin->is_emergency => "⚠ {$techName} llegó al equipo {$equipCode} (emergencia)",
                default => "{$techName} llegó al equipo {$equipCode}",
            },
        ];
    }

    /** Por que un check-in sin conexion necesita que el master lo mire. */
    private function reviewReason(TechnicianCheckin $checkin): string
    {
        return match (true) {
            $checkin->access_status === 'reloj_inconsistente' => 'la hora del teléfono no coincide con la del servidor',
            $checkin->access_status === 'anticipado' => 'se registró antes de la hora de la visita',
            $checkin->access_status === 'sin_programar' => 'no había visita programada para ese equipo',
            $checkin->offline_check === 'sin_agenda' => 'el teléfono no tenía la agenda del día para verificar la hora',
            default => 'registro sin verificar',
        };
    }

    public function toWhatsApp(object $notifiable): array
    {
        $checkin = $this->checkin->fresh([
            'equipment.site.client',
            'technician',
        ]);

        return [
            'template' => 'checkin_notification',
            'parameters' => [
                $checkin->technician->name,
                $checkin->equipment->internal_code,
                $checkin->equipment->site?->client?->business_name ?? '',
                $checkin->checked_in_at->format('d/m/Y H:i'),
            ],
        ];
    }
}
