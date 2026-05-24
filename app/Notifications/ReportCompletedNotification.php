<?php

namespace App\Notifications;

use App\Channels\WhatsAppChannel;
use App\Models\ServiceReport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReportCompletedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private ServiceReport $report,
        private string $recipientRole = 'master', // master, technician, client
        private ?string $pdfContent = null,
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
        $report = $this->report->fresh([
            'equipment.site.client',
            'technician',
            'site',
            'client',
        ]);

        $typeLabels = ['RSTP' => 'Preventivo', 'RSTC' => 'Correctivo', 'RSTE' => 'Especial'];
        $typeLabel = $typeLabels[$report->report_type] ?? $report->report_type;
        $equipCode = $report->equipment?->internal_code ?? '—';
        $clientName = $report->client?->business_name ?? '—';
        $siteName = $report->site?->name ?? '—';
        $techName = $report->technician?->name ?? '—';
        $serviceDate = $report->service_date?->format('d/m/Y') ?? '—';

        $mail = (new MailMessage)
            ->subject("Informe {$report->report_number} — Ascensores Tzion")
            ->greeting("Informe de servicio completado")
            ->line("**Número:** {$report->report_number}")
            ->line("**Tipo:** {$typeLabel}")
            ->line("**Equipo:** {$equipCode}")
            ->line("**Cliente:** {$clientName}")
            ->line("**Sede:** {$siteName}")
            ->line("**Técnico:** {$techName}")
            ->line("**Fecha:** {$serviceDate}")
            ->salutation('Equipo de Ascensores Tzion');

        // Para el cliente: agregar boton de confirmacion de recepcion
        if ($this->recipientRole === 'client' && $report->reception_token) {
            $confirmUrl = url("/confirmacion-reporte/{$report->reception_token}");
            $mail->action('Confirmar Recepción del Reporte', $confirmUrl)
                ->line('Haga clic en el botón para confirmar que recibió este informe.');
        }

        // Adjuntar PDF si está disponible
        if ($this->pdfContent) {
            $mail->attachData(
                $this->pdfContent,
                "{$report->report_number}.pdf",
                ['mime' => 'application/pdf'],
            );
        }

        return $mail;
    }

    public function toArray(object $notifiable): array
    {
        $report = $this->report->load([
            'equipment:id,internal_code',
            'client:id,business_name',
            'technician:id,name',
        ]);

        $typeLabels = ['RSTP' => 'Preventivo', 'RSTC' => 'Correctivo', 'RSTE' => 'Especial'];
        $typeLabel = $typeLabels[$report->report_type] ?? $report->report_type;

        return [
            'type' => 'report_completed',
            'report_id' => $report->id,
            'report_number' => $report->report_number,
            'report_type' => $report->report_type,
            'report_type_label' => $typeLabel,
            'equipment_code' => $report->equipment?->internal_code,
            'client_name' => $report->client?->business_name,
            'technician_name' => $report->technician?->name,
            'message' => "Informe {$report->report_number} ({$typeLabel}) completado",
        ];
    }

    public function toWhatsApp(object $notifiable): array
    {
        $report = $this->report->load([
            'equipment:id,internal_code',
            'client:id,business_name',
            'site:id,name',
            'technician:id,name',
        ]);

        return [
            'template' => 'report_completed',
            'parameters' => [
                $report->report_number,
                $report->report_type,
                $report->equipment?->internal_code ?? '',
                $report->client?->business_name ?? '',
                $report->technician?->name ?? '',
                $report->service_date,
            ],
        ];
    }
}
