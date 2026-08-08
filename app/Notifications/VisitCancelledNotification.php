<?php

namespace App\Notifications;

use App\Models\ScheduledVisit;
use App\Notifications\Concerns\DescribesVisit;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Se anulo una visita programada.
 */
class VisitCancelledNotification extends Notification implements ShouldQueue
{
    use DescribesVisit, Queueable;

    public function __construct(private ScheduledVisit $visit) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $visit = $this->visitWithRelations($this->visit);

        $equipment = $visit->equipment?->internal_code ?? '—';
        $site = $visit->site?->name ?? '—';
        $when = $this->whenLabel($visit);

        $mail = (new MailMessage)
            ->subject("Visita cancelada — {$equipment} — {$this->dateLabel($visit)}")
            ->greeting('Se canceló una visita programada')
            ->line("**Estaba prevista para:** {$when}")
            ->line("**Equipo:** {$equipment}")
            ->line("**Sede:** {$site}");

        if ($visit->cancel_reason) {
            $mail->line("**Motivo:** {$visit->cancel_reason}");
        }

        return $mail
            ->line('Nos pondremos en contacto para acordar una nueva fecha.')
            ->action('Ver el cronograma', $this->deepLink($notifiable))
            ->salutation('Equipo de Ascensores Tzion');
    }

    public function toArray(object $notifiable): array
    {
        $visit = $this->visitWithRelations($this->visit);
        $equipment = $visit->equipment?->internal_code ?? 'equipo';

        return array_merge($this->visitPayload($visit), [
            'type' => 'visit_cancelled',
            'cancel_reason' => $visit->cancel_reason,
            'message' => "Se canceló la visita de {$equipment} del {$this->dateLabel($visit)}",
        ]);
    }
}
