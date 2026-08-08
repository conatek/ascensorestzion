<?php

namespace App\Notifications;

use App\Models\ScheduledVisit;
use App\Notifications\Concerns\DescribesVisit;
use Carbon\CarbonImmutable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * La visita se movio. Lleva la fecha anterior porque el destinatario ya la tenia
 * apuntada: sin ella el correo no se distingue de uno de visita nueva.
 */
class VisitRescheduledNotification extends Notification implements ShouldQueue
{
    use DescribesVisit, Queueable;

    public function __construct(
        private ScheduledVisit $visit,
        private CarbonImmutable $previousStart,
    ) {}

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
        $technician = $visit->technician?->name ?? 'por asignar';
        $before = $this->previousStart->format('d/m/Y H:i');
        $when = $this->whenLabel($visit);

        $mail = (new MailMessage)
            ->subject("Visita reprogramada — {$equipment} — {$this->dateLabel($visit)}")
            ->greeting('Cambió la fecha de una visita')
            ->line("**Antes:** {$before}")
            ->line("**Ahora:** {$when}")
            ->line("**Equipo:** {$equipment}")
            ->line("**Sede:** {$site}");

        if (! $isTechnician) {
            $mail->line("**Técnico asignado:** {$technician}");
        }

        return $mail
            ->action($isTechnician ? 'Ver mi agenda' : 'Ver mi cronograma', $this->deepLink($notifiable))
            ->salutation('Equipo de Ascensores Tzion');
    }

    public function toArray(object $notifiable): array
    {
        $visit = $this->visitWithRelations($this->visit);
        $equipment = $visit->equipment?->internal_code ?? 'equipo';
        $before = $this->previousStart->format('d/m/Y');

        return array_merge($this->visitPayload($visit), [
            'type' => 'visit_rescheduled',
            'previous_start' => $this->previousStart->toIso8601String(),
            'message' => "La visita de {$equipment} pasó del {$before} al {$this->dateLabel($visit)}",
        ]);
    }
}
