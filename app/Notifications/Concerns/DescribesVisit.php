<?php

namespace App\Notifications\Concerns;

use App\Models\ScheduledVisit;
use App\Models\User;
use Illuminate\Notifications\AnonymousNotifiable;

/**
 * Lo que comparten las notificaciones del cronograma: como se describe una visita
 * y a donde lleva el enlace segun quien la reciba.
 */
trait DescribesVisit
{
    private const DAYS = [
        1 => 'lunes', 2 => 'martes', 3 => 'miércoles', 4 => 'jueves',
        5 => 'viernes', 6 => 'sábado', 7 => 'domingo',
    ];

    private const MONTHS = [
        1 => 'enero', 2 => 'febrero', 3 => 'marzo', 4 => 'abril', 5 => 'mayo', 6 => 'junio',
        7 => 'julio', 8 => 'agosto', 9 => 'septiembre', 10 => 'octubre', 11 => 'noviembre', 12 => 'diciembre',
    ];

    /**
     * La visita con sus relaciones. `fresh()` y no `load()`: la notificacion se
     * ejecuta encolada, a veces minutos despues, y el modelo serializado puede
     * traer datos viejos.
     */
    protected function visitWithRelations(ScheduledVisit $visit): ScheduledVisit
    {
        return $visit->fresh([
            'equipment:id,internal_code,equipment_type',
            'site:id,name,address,city',
            'client:id,business_name',
            'technician:id,name,phone',
        ]) ?? $visit;
    }

    /** "jueves 14 de agosto" */
    protected function longDate($date): string
    {
        $day = self::DAYS[$date->dayOfWeekIso] ?? '';
        $month = self::MONTHS[(int) $date->format('n')] ?? '';

        return sprintf('%s %d de %s', $day, $date->day, $month);
    }

    /**
     * "jueves 14 de agosto, 08:00–09:30". Es el formato de fecha del proyecto:
     * el mismo en todos los correos y en las dos mitades del de reprogramación.
     */
    protected function longDateTime($start, $end = null): string
    {
        $range = $end
            ? sprintf('%s–%s', $start->format('H:i'), $end->format('H:i'))
            : $start->format('H:i');

        return $this->longDate($start).', '.$range;
    }

    /** "jueves 14 de agosto, 08:00–09:30" para la visita. */
    protected function whenLabel(ScheduledVisit $visit): string
    {
        return $this->longDateTime($visit->scheduled_start, $visit->scheduled_end);
    }

    /**
     * Asunto: primero lo que le importa a quien lo recibe (qué y cuándo) y el
     * código del equipo al final, como referencia para buscarlo en la bandeja.
     */
    protected function subjectWithRef(string $headline, ScheduledVisit $visit): string
    {
        $code = $visit->equipment?->internal_code;

        return $code ? "{$headline} ({$code})" : $headline;
    }

    /**
     * ¿El destinatario es un técnico? Solo un usuario con ese rol. Un correo de
     * notificación suelto (AnonymousNotifiable) NUNCA es técnico: es lado cliente.
     */
    protected function isTechnician(object $notifiable): bool
    {
        return $notifiable instanceof User && $notifiable->hasRole('technician');
    }

    /**
     * Cada rol tiene su pantalla: el cliente el portal, el tecnico su agenda y
     * coordinacion el tablero. Un enlace unico dejaria a alguien en un 403.
     * Un correo de notificación suelto (sin login) se trata como lado cliente.
     */
    protected function deepLink(object $notifiable): string
    {
        if ($this->isTechnician($notifiable)) {
            return url('/tech/agenda');
        }

        // Usuario interno (master/coordinador/super) → tablero.
        if ($notifiable instanceof User && ! $notifiable->hasRole('admin')) {
            return url('/cronograma');
        }

        // Admin del cliente o correo de notificación suelto → portal.
        return url('/portal/cronograma');
    }

    /**
     * Canales para las notificaciones del cronograma: un correo suelto solo recibe
     * mail (no tiene fila en `notifications` ni teléfono); un usuario, base+mail.
     *
     * @return array<int, string>
     */
    protected function scheduleChannels(object $notifiable): array
    {
        return $notifiable instanceof AnonymousNotifiable ? ['mail'] : ['database', 'mail'];
    }

    /**
     * Datos comunes de la campana. El frontend enruta por `type`.
     *
     * @return array<string, mixed>
     */
    protected function visitPayload(ScheduledVisit $visit): array
    {
        return [
            'visit_id' => $visit->id,
            'visit_uuid' => $visit->uuid,
            'scheduled_start' => $visit->scheduled_start?->toIso8601String(),
            'equipment_code' => $visit->equipment?->internal_code,
            'site_name' => $visit->site?->name,
            'client_name' => $visit->client?->business_name,
            'technician_name' => $visit->technician?->name,
        ];
    }
}
