<?php

namespace App\Notifications;

use App\Models\RescheduleRequest;
use App\Notifications\Concerns\DescribesVisit;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Un cliente pide mover su visita. Va a coordinacion, que es quien decide.
 *
 * Es la unica notificacion del cronograma que llega a master y coordinator: las
 * demas avisan de acciones suyas, esta espera una respuesta suya.
 */
class RescheduleRequestedNotification extends Notification implements ShouldQueue
{
    use DescribesVisit, Queueable;

    public function __construct(private RescheduleRequest $request) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $request = $this->request->fresh(['scheduledVisit', 'requester:id,name']) ?? $this->request;
        $visit = $this->visitWithRelations($request->scheduledVisit);

        $client = $visit->client?->business_name ?? '—';
        $equipment = $visit->equipment?->internal_code ?? '—';
        $site = $visit->site?->name ?? '—';
        $technician = $visit->technician?->name ?? 'por asignar';

        $mail = (new MailMessage)
            ->subject($this->subjectWithRef(
                'Solicitud de reprogramación para el '.$this->longDate($request->original_start),
                $visit,
            ))
            ->greeting('Un cliente pide mover una visita')
            ->line("**Cliente:** {$client}")
            ->line('**Actual:** '.$this->longDateTime($request->original_start, $visit->scheduled_end))
            ->line('**Propone:** '.$this->longDateTime($request->proposed_start, $request->proposed_end))
            ->line("**Equipo:** {$equipment}")
            ->line("**Sede:** {$site}")
            ->line("**Técnico:** {$technician}");

        if ($request->reason) {
            $mail->line("**Motivo:** {$request->reason}");
        }

        return $mail
            ->line('Lo pidió '.($request->requester?->name ?? 'el cliente').'.')
            // El parametro abre la bandeja al montar el tablero: quien viene de
            // este correo viene a decidir, no a mirar el calendario.
            ->action('Revisar la solicitud', $this->deepLink($notifiable).'?solicitudes=1')
            ->salutation('Equipo de Ascensores Tzion');
    }

    public function toArray(object $notifiable): array
    {
        $request = $this->request->fresh(['scheduledVisit', 'requester:id,name']) ?? $this->request;
        $visit = $this->visitWithRelations($request->scheduledVisit);

        $client = $visit->client?->business_name ?? 'Un cliente';
        $equipment = $visit->equipment?->internal_code ?? 'una visita';

        return array_merge($this->visitPayload($visit), [
            'type' => 'reschedule_requested',
            'request_id' => $request->id,
            'proposed_start' => $request->proposed_start?->toIso8601String(),
            'message' => "{$client} pide mover la visita de {$equipment} al ".$this->longDate($request->proposed_start),
        ]);
    }
}
