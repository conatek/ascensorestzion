<?php

namespace App\Notifications;

use App\Models\ServiceReport;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Envía el reporte de servicio (PDF adjunto) por correo, con el diseño estándar
 * del tema de correos (logo y pie corporativo). Se envía de forma síncrona porque
 * el PDF ya viene generado en la misma petición; no se encola para no serializar
 * el PDF ni el modelo con sus relaciones.
 */
class ServiceReportReadyNotification extends Notification
{
    public function __construct(
        private ServiceReport $report,
        private string $pdf,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $report = $this->report->loadMissing(['equipment', 'client', 'site']);

        return (new MailMessage)
            ->subject("Reporte {$report->report_number} — Ascensores Tzion")
            ->greeting('Reporte de servicio técnico')
            ->line('Adjuntamos el reporte de servicio técnico con los siguientes datos:')
            ->line("**N.° de reporte:** {$report->report_number}")
            ->line("**Tipo:** {$report->report_type}")
            ->line('**Fecha:** '.$report->service_date->format('d/m/Y'))
            ->line('**Equipo:** '.($report->equipment->internal_code ?? '—'))
            ->line('**Cliente:** '.($report->client->business_name ?? '—'))
            ->line('**Sede:** '.($report->site->name ?? '—'))
            ->line('El PDF completo va adjunto a este correo.')
            ->salutation('Equipo de Ascensores Tzion')
            ->attachData($this->pdf, "{$report->report_number}.pdf", [
                'mime' => 'application/pdf',
            ]);
    }
}
