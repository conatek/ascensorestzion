<?php

namespace App\Notifications;

use App\Models\ScheduledVisit;
use App\Notifications\Concerns\DescribesVisit;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Se programo una visita nueva. Va al cliente y al tecnico asignado.
 */
class VisitScheduledNotification extends Notification implements ShouldQueue
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
        $isTechnician = $notifiable->hasRole('technician');

        $equipment = $visit->equipment?->internal_code ?? '—';
        $site = $visit->site?->name ?? '—';
        $address = $visit->site?->address ?? '';
        $client = $visit->client?->business_name ?? '—';
        $technician = $visit->technician?->name ?? 'por asignar';
        $when = $this->whenLabel($visit);

        $mail = (new MailMessage)
            ->subject("Visita programada — {$equipment} — {$this->dateLabel($visit)}")
            ->greeting($isTechnician ? 'Tienes una visita nueva en tu agenda' : 'Hemos programado tu próxima visita')
            ->line("**Cuándo:** {$when}")
            ->line("**Equipo:** {$equipment}")
            ->line("**Sede:** {$site}");

        if ($address) {
            $mail->line("**Dirección:** {$address}");
        }

        $mail->line($isTechnician ? "**Cliente:** {$client}" : "**Técnico asignado:** {$technician}");

        if ($visit->notes) {
            $mail->line("**Notas:** {$visit->notes}");
        }

        return $mail
            ->action($isTechnician ? 'Ver mi agenda' : 'Ver mi cronograma', $this->deepLink($notifiable))
            ->salutation('Equipo de Ascensores Tzion');
    }

    public function toArray(object $notifiable): array
    {
        $visit = $this->visitWithRelations($this->visit);
        $equipment = $visit->equipment?->internal_code ?? 'equipo';

        return array_merge($this->visitPayload($visit), [
            'type' => 'visit_scheduled',
            'message' => "Visita programada para {$equipment} el {$this->dateLabel($visit)}",
        ]);
    }
}
