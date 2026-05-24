<?php

namespace App\Notifications;

use App\Channels\WhatsAppChannel;
use App\Models\ServiceReport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReportReceptionConfirmedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private ServiceReport $report,
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
        $report = $this->report->fresh(['client']);

        return (new MailMessage)
            ->subject("Recepción confirmada — {$report->report_number}")
            ->greeting('Confirmación de recepción')
            ->line("El cliente **{$report->client?->business_name}** ha confirmado la recepción del informe **{$report->report_number}**.")
            ->line("**Confirmado el:** {$report->reception_confirmed_at?->format('d/m/Y h:i A')}")
            ->line("El informe ha sido cerrado automáticamente.")
            ->salutation('Equipo de Ascensores Tzion');
    }

    public function toArray(object $notifiable): array
    {
        $report = $this->report->load([
            'client:id,business_name',
        ]);

        return [
            'type' => 'report_reception_confirmed',
            'report_id' => $report->id,
            'report_number' => $report->report_number,
            'client_name' => $report->client?->business_name,
            'confirmed_at' => $report->reception_confirmed_at?->toIso8601String(),
            'message' => "El cliente confirmó recepción del informe {$report->report_number}",
        ];
    }

    public function toWhatsApp(object $notifiable): array
    {
        $report = $this->report->load('client:id,business_name');

        return [
            'template' => 'reception_confirmed',
            'parameters' => [
                $report->report_number,
                $report->client?->business_name ?? '',
                $report->reception_confirmed_at?->format('d/m/Y H:i') ?? '',
            ],
        ];
    }
}
