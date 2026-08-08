<?php

namespace App\Notifications;

use App\Models\RescheduleRequest;
use App\Notifications\Concerns\DescribesVisit;
use Carbon\CarbonImmutable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Coordinacion resolvio la solicitud. Va al cliente y al tecnico.
 *
 * Cuando se aprueba, este es el UNICO correo del cambio: `reschedule()` se llama
 * con notify:false para que no salga ademas el VisitRescheduledNotification. Por
 * eso el cuerpo de la version aprobada lleva tambien el "Antes / Ahora" — quien lo
 * recibe necesita las dos cosas: que le dijeron que si, y a que hora queda.
 */
class RescheduleResolvedNotification extends Notification implements ShouldQueue
{
    use DescribesVisit, Queueable;

    public function __construct(private RescheduleRequest $request) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $request = $this->request->fresh(['scheduledVisit']) ?? $this->request;
        $visit = $this->visitWithRelations($request->scheduledVisit);
        $isTechnician = $notifiable->hasRole('technician');

        $mail = $request->status === RescheduleRequest::APROBADA
            ? $this->approvedMail($request, $visit, $isTechnician)
            : $this->rejectedMail($request, $visit, $isTechnician);

        $mail->line('**Equipo:** '.($visit->equipment?->internal_code ?? '—'))
            ->line('**Sede:** '.($visit->site?->name ?? '—'));

        if (! $isTechnician) {
            $mail->line('**Técnico asignado:** '.($visit->technician?->name ?? 'por asignar'));
        }

        if ($request->resolution_notes) {
            $mail->line("**Nota de coordinación:** {$request->resolution_notes}");
        }

        return $mail
            ->action($isTechnician ? 'Ver mi agenda' : 'Ver mi cronograma', $this->deepLink($notifiable))
            ->salutation('Equipo de Ascensores Tzion');
    }

    private function approvedMail(RescheduleRequest $request, $visit, bool $isTechnician): MailMessage
    {
        $headline = $isTechnician
            ? 'Visita movida al '.$this->longDate($request->proposed_start)
            : 'Aprobamos el cambio: tu visita pasa al '.$this->longDate($request->proposed_start);

        return (new MailMessage)
            ->subject($this->subjectWithRef($headline, $visit))
            ->greeting($isTechnician
                ? 'Se aprobó una reprogramación'
                : 'Listo, movimos tu visita')
            ->line('**Antes:** '.$this->longDateTime($request->original_start, $this->originalEnd($request)))
            ->line('**Ahora:** '.$this->longDateTime($request->proposed_start, $request->proposed_end));
    }

    private function rejectedMail(RescheduleRequest $request, $visit, bool $isTechnician): MailMessage
    {
        return (new MailMessage)
            ->subject($this->subjectWithRef(
                'No pudimos mover la visita del '.$this->longDate($request->original_start),
                $visit,
            ))
            ->greeting($isTechnician ? 'Una reprogramación no salió adelante' : 'No pudimos mover tu visita')
            ->line('**Pediste:** '.$this->longDateTime($request->proposed_start, $request->proposed_end))
            ->line('**Sigue siendo:** '.$this->whenLabel($visit));
    }

    /**
     * La duracion se conserva al reprogramar, asi que el fin original se deriva del
     * rango propuesto. No se guarda en la tabla porque seria un dato redundante que
     * podria quedar desalineado.
     */
    private function originalEnd(RescheduleRequest $request): CarbonImmutable
    {
        $minutes = CarbonImmutable::parse($request->proposed_start)
            ->diffInMinutes(CarbonImmutable::parse($request->proposed_end));

        return CarbonImmutable::parse($request->original_start)->addMinutes((int) $minutes);
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
