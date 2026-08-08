<?php

namespace App\Notifications;

use App\Models\RescheduleRequest;
use App\Notifications\Concerns\DescribesVisit;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Coordinacion resolvio la solicitud. Va al cliente y al tecnico.
 */
class RescheduleResolvedNotification extends Notification implements ShouldQueue
{
    use DescribesVisit, Queueable;

    public function __construct(private RescheduleRequest $request) {}

    /**
     * Aprobada no lleva correo: reschedule() ya mando el de "Antes / Ahora" en el
     * mismo segundo, y dos correos diciendo lo mismo hacen que se dejen de leer
     * los dos. Rechazada si, porque es el unico aviso que hay.
     */
    public function via(object $notifiable): array
    {
        return $this->request->status === RescheduleRequest::APROBADA
            ? ['database']
            : ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $request = $this->request->fresh(['scheduledVisit']) ?? $this->request;
        $visit = $this->visitWithRelations($request->scheduledVisit);
        $isTechnician = $notifiable->hasRole('technician');

        $mail = (new MailMessage)
            ->subject($this->subjectWithRef(
                'No pudimos mover la visita del '.$this->longDate($request->original_start),
                $visit,
            ))
            ->greeting($isTechnician ? 'Una reprogramación no salió adelante' : 'No pudimos mover tu visita')
            ->line('**Pediste:** '.$this->longDateTime($request->proposed_start, $request->proposed_end))
            ->line('**Sigue siendo:** '.$this->whenLabel($visit))
            ->line('**Equipo:** '.($visit->equipment?->internal_code ?? '—'))
            ->line('**Sede:** '.($visit->site?->name ?? '—'));

        if ($request->resolution_notes) {
            $mail->line("**Motivo:** {$request->resolution_notes}");
        }

        return $mail
            ->action($isTechnician ? 'Ver mi agenda' : 'Ver mi cronograma', $this->deepLink($notifiable))
            ->salutation('Equipo de Ascensores Tzion');
    }

    public function toArray(object $notifiable): array
    {
        $request = $this->request->fresh(['scheduledVisit']) ?? $this->request;
        $visit = $this->visitWithRelations($request->scheduledVisit);
        $equipment = $visit->equipment?->internal_code ?? 'la visita';

        $message = $request->status === RescheduleRequest::APROBADA
            ? "Se aprobó mover la visita de {$equipment} al ".$this->longDate($request->proposed_start)
            : "No se aprobó mover la visita de {$equipment}: sigue el ".$this->longDate($visit->scheduled_start);

        return array_merge($this->visitPayload($visit), [
            'type' => 'reschedule_resolved',
            'request_id' => $request->id,
            'resolution' => $request->status,
            'message' => $message,
        ]);
    }
}
