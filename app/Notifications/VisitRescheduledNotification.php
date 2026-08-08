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
        // El fin anterior solo sirve para que "Antes" y "Ahora" se lean con el
        // mismo formato; sin el, una linea saldria con rango y la otra sin el.
        private ?CarbonImmutable $previousEnd = null,
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
        $before = $this->longDateTime($this->previousStart, $this->previousEnd);
        $when = $this->whenLabel($visit);

        $mail = (new MailMessage)
            ->subject($this->subjectWithRef(
                ($isTechnician ? 'Visita movida al ' : 'Tu visita cambió al ').$this->longDate($visit->scheduled_start),
                $visit,
            ))
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
        $before = $this->longDate($this->previousStart);

        return array_merge($this->visitPayload($visit), [
            'type' => 'visit_rescheduled',
            'previous_start' => $this->previousStart->toIso8601String(),
            'message' => "La visita de {$equipment} pasó del {$before} al {$this->longDate($visit->scheduled_start)}",
        ]);
    }
}
