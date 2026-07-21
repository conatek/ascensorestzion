<?php

namespace App\Notifications;

use App\Channels\WhatsAppChannel;
use App\Models\ServiceReport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Notificacion agrupada de una visita firmada en lote (firma diferida):
 * un solo mensaje para los N informes de la misma sede y dia.
 */
class VisitReportsCompletedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    private const TYPE_LABELS = ['RSTP' => 'Preventivo', 'RSTC' => 'Correctivo', 'RSTE' => 'Especial'];

    public function __construct(
        private array $reportIds,
        private string $recipientRole = 'master', // master, technician, client
    ) {}

    public function via(object $notifiable): array
    {
        $channels = ['database', 'mail'];

        if ($notifiable->phone) {
            $channels[] = WhatsAppChannel::class;
        }

        return $channels;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, ServiceReport>
     */
    private function reports()
    {
        return ServiceReport::with(['equipment:id,internal_code', 'client:id,business_name', 'site:id,name', 'technician:id,name'])
            ->whereIn('id', $this->reportIds)
            ->orderBy('report_number')
            ->get();
    }

    public function toMail(object $notifiable): MailMessage
    {
        $reports = $this->reports();
        $first = $reports->first();
        $count = $reports->count();

        $clientName = $first?->client?->business_name ?? '—';
        $siteName = $first?->site?->name ?? '—';
        $techName = $first?->technician?->name ?? '—';
        $serviceDate = $first?->service_date?->format('d/m/Y') ?? '—';

        $mail = (new MailMessage)
            ->subject("{$count} informes de servicio — {$siteName} — Ascensores Tzion")
            ->greeting('Visita de servicio completada')
            ->line("**Cliente:** {$clientName}")
            ->line("**Sede:** {$siteName}")
            ->line("**Técnico:** {$techName}")
            ->line("**Fecha:** {$serviceDate}")
            ->line("**Informes firmados:** {$count}");

        // Para el cliente cada informe lleva su enlace de descarga (PDF) y el de
        // confirmacion de recepcion; para el resto basta el listado.
        foreach ($reports as $report) {
            $typeLabel = self::TYPE_LABELS[$report->report_type] ?? $report->report_type;
            $equipCode = $report->equipment?->internal_code ?? '—';

            if ($this->recipientRole === 'client' && $report->reception_token) {
                $pdfUrl = url("/api/report-confirmation/{$report->reception_token}/pdf");
                $confirmUrl = url("/confirmacion-reporte/{$report->reception_token}");
                $mail->line(
                    "- **{$report->report_number}** ({$typeLabel}) — Equipo {$equipCode} · "
                    ."[Descargar PDF]({$pdfUrl}) · [Confirmar recepción]({$confirmUrl})"
                );
            } else {
                $mail->line("- **{$report->report_number}** ({$typeLabel}) — Equipo {$equipCode}");
            }
        }

        if ($this->recipientRole === 'client') {
            $mail->line('Descargue cada informe y confirme su recepción desde los enlaces de arriba.');
        }

        return $mail->salutation('Equipo de Ascensores Tzion');
    }

    public function toArray(object $notifiable): array
    {
        $reports = $this->reports();
        $first = $reports->first();
        $count = $reports->count();
        $siteName = $first?->site?->name ?? '—';

        return [
            // Se reutiliza el tipo report_completed para que la campana de
            // notificaciones lo pinte y enrute como un informe normal.
            'type' => 'report_completed',
            'report_id' => $first?->id,
            'report_ids' => $reports->pluck('id')->all(),
            'report_number' => $first?->report_number,
            'report_type' => $first?->report_type,
            'report_count' => $count,
            'equipment_code' => $first?->equipment?->internal_code,
            'client_name' => $first?->client?->business_name,
            'technician_name' => $first?->technician?->name,
            'message' => "{$count} informes completados en {$siteName}",
        ];
    }

    public function toWhatsApp(object $notifiable): array
    {
        $reports = $this->reports();
        $first = $reports->first();

        return [
            'template' => 'visit_reports_completed',
            'parameters' => [
                (string) $reports->count(),
                $first?->site?->name ?? '',
                $first?->client?->business_name ?? '',
                $first?->technician?->name ?? '',
                $first?->service_date?->format('d/m/Y') ?? '',
            ],
        ];
    }
}
