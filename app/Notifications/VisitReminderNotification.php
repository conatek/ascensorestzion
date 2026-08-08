<?php

namespace App\Notifications;

use App\Models\VisitReminder;
use App\Notifications\Concerns\DescribesVisit;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * El recordatorio en si. Los canales no los decide la notificacion sino la fila
 * de visit_reminders: se fijaron al materializarla, cuando se sabia el opt-in del
 * destinatario.
 */
class VisitReminderNotification extends Notification implements ShouldQueue
{
    use DescribesVisit, Queueable;

    public function __construct(private VisitReminder $reminder) {}

    public function via(object $notifiable): array
    {
        return $this->reminder->channels ?: ['database', 'mail'];
    }

    /** Para que el listener sepa qué fila marcar si la entrega falla. */
    public function reminderId(): ?int
    {
        return $this->reminder->id;
    }

    public function toMail(object $notifiable): MailMessage
    {
        $visit = $this->visitWithRelations($this->reminder->scheduledVisit);
        $isTechnician = $notifiable->hasRole('technician');

        $equipment = $visit->equipment?->internal_code ?? '—';
        $site = $visit->site?->name ?? '—';
        $address = $visit->site?->address ?? '';
        $client = $visit->client?->business_name ?? '—';
        $technician = $visit->technician?->name ?? 'por asignar';
        $when = $this->whenLabel($visit);
        $countdown = $this->countdownLabel($visit->scheduled_start);

        $mail = (new MailMessage)
            ->subject("Recordatorio: visita {$countdown} — {$equipment}")
            ->greeting($isTechnician ? "Tu próxima visita es {$countdown}" : "Tu mantenimiento es {$countdown}")
            ->line("**Cuándo:** {$when}")
            ->line("**Equipo:** {$equipment}")
            ->line("**Sede:** {$site}");

        if ($address) {
            $mail->line("**Dirección:** {$address}");
        }

        $mail->line($isTechnician ? "**Cliente:** {$client}" : "**Técnico asignado:** {$technician}");

        return $mail
            ->action($isTechnician ? 'Ver mi agenda' : 'Ver mi cronograma', $this->deepLink($notifiable))
            ->salutation('Equipo de Ascensores Tzion');
    }

    public function toArray(object $notifiable): array
    {
        $visit = $this->visitWithRelations($this->reminder->scheduledVisit);
        $equipment = $visit->equipment?->internal_code ?? 'equipo';
        $countdown = $this->countdownLabel($visit->scheduled_start);

        return array_merge($this->visitPayload($visit), [
            'type' => 'visit_reminder',
            'reminder_id' => $this->reminder->id,
            'message' => "Recordatorio: visita de {$equipment} {$countdown}",
        ]);
    }

    /** "hoy", "mañana" o "en 7 días" — se cuenta por días de calendario. */
    private function countdownLabel($start): string
    {
        $days = now()->startOfDay()->diffInDays($start->copy()->startOfDay(), false);

        return match (true) {
            $days <= 0 => 'hoy',
            $days === 1 => 'mañana',
            default => "en {$days} días",
        };
    }
}
