<?php

namespace App\Services;

use App\Models\Equipment;
use App\Models\RescheduleRequest;
use App\Models\ScheduledVisit;
use App\Models\ScheduleException;
use App\Models\ScheduleSetting;
use App\Models\ServiceReport;
use App\Models\TechnicianSchedule;
use App\Models\User;
use App\Models\VisitReminder;
use App\Models\VisitReminderSetting;
use App\Notifications\VisitCancelledNotification;
use App\Notifications\VisitRescheduledNotification;
use App\Notifications\VisitScheduledNotification;
use Carbon\CarbonImmutable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification as NotificationFacade;
use Illuminate\Validation\ValidationException;

/**
 * Reglas del cronograma: jornada, duracion y validacion de una cita.
 *
 * Es la unica fuente de verdad de la jornada. La usan la validacion al crear/mover
 * una visita, el grid del calendario y (desde la fase 4) el calculo de espacios
 * libres del portal; si cada uno resolviera la jornada por su cuenta acabarian
 * discrepando y el portal ofreceria horarios que el backend rechaza.
 */
class ScheduleService
{
    /**
     * Jornada efectiva de un tecnico: la global con los overrides propios encima y,
     * si se pasa fecha, la excepcion de ese dia por encima de todo.
     *
     * Cada campo se resuelve por separado a proposito, para poder cambiarle a un
     * tecnico solo el horario y que siga heredando los dias.
     *
     * Sin `$date` devuelve la jornada habitual, que es lo que necesitan el grid del
     * calendario y la ficha del tecnico. Con fecha devuelve la del dia concreto,
     * que es lo que tienen que mirar la validacion y la disponibilidad.
     *
     * @return array{days: int[], start: string, end: string, break_start: ?string, break_end: ?string, exception: ?array{type: string, note: ?string}}
     */
    public function workingWindowFor(User $technician, ?CarbonImmutable $date = null): array
    {
        $hours = ScheduleSetting::get('working_hours');
        $break = ScheduleSetting::get('working_break');

        $window = [
            'days' => ScheduleSetting::get('working_days'),
            'start' => $hours['start'] ?? '08:00',
            'end' => $hours['end'] ?? '18:00',
            'break_start' => $break['start'] ?? null,
            'break_end' => $break['end'] ?? null,
            'exception' => null,
        ];

        $own = TechnicianSchedule::query()->where('user_id', $technician->id)->first();

        if ($own && $own->enabled) {
            if (! empty($own->working_days)) {
                $window['days'] = $own->working_days;
            }

            if (! empty($own->working_hours['start']) && ! empty($own->working_hours['end'])) {
                $window['start'] = $own->working_hours['start'];
                $window['end'] = $own->working_hours['end'];
            }

            // El descanso se sobrescribe en bloque: una fila con break_start nulo es un
            // tecnico SIN descanso, no un tecnico que hereda el descanso global. Si no
            // fuera asi no habria forma de quitarselo a nadie.
            $window['break_start'] = $this->normalizeTime($own->break_start);
            $window['break_end'] = $this->normalizeTime($own->break_end);
        }

        if (! $date) {
            return $window;
        }

        return $this->applyException(
            $window,
            ScheduleException::resolveFor($technician->id, $date),
            $date,
        );
    }

    /**
     * Superpone la excepcion de un dia sobre la jornada habitual.
     *
     * Va suelto y es publico porque el calculo de disponibilidad recorre treinta
     * dias con las excepciones ya cargadas de una vez: si tuviera que llamar a
     * workingWindowFor() por dia serian sesenta consultas para pintar unos chips.
     *
     * @param  array<string, mixed>  $window
     * @return array<string, mixed>
     */
    public function applyException(array $window, ?ScheduleException $exception, CarbonImmutable $date): array
    {
        if (! $exception) {
            return $window;
        }

        if ($exception->isClosed()) {
            // Sin dias laborables no cabe nada: la validacion y la disponibilidad
            // ya tratan "no es un dia laborable" como no agendable.
            $window['days'] = [];
            $window['exception'] = ['type' => 'cerrado', 'note' => $exception->note];

            return $window;
        }

        // Un horario especial habilita el dia aunque no fuera laborable — que es
        // justo el caso de "este sabado sí se trabaja".
        $window['days'] = array_values(array_unique([...$window['days'], $date->dayOfWeekIso]));
        $window['start'] = $exception->working_hours['start'];
        $window['end'] = $exception->working_hours['end'];
        $window['exception'] = ['type' => 'personalizada', 'note' => $exception->note];

        return $window;
    }

    /**
     * Duracion por defecto en minutos: la del equipo si la tiene, si no la global.
     * El tercer nivel (el rango explicito de la visita) lo decide quien agenda.
     */
    public function defaultDurationFor(?Equipment $equipment = null): int
    {
        return $equipment?->default_visit_duration_minutes
            ?: (int) ScheduleSetting::get('default_duration_minutes');
    }

    /**
     * Programa una visita. Deriva sede y cliente del equipo (columnas desnormalizadas,
     * patron de service_reports) para no depender de que quien llama los mande bien.
     *
     * @param  array{equipment_id:int, technician_id:int, scheduled_start:string, scheduled_end:string, visit_type?:string, notes?:string}  $data
     *
     * @throws ValidationException
     */
    public function create(array $data, User $actor): ScheduledVisit
    {
        $equipment = Equipment::with('site')->findOrFail($data['equipment_id']);
        $technician = User::findOrFail($data['technician_id']);
        $start = CarbonImmutable::parse($data['scheduled_start']);
        $end = CarbonImmutable::parse($data['scheduled_end']);

        $this->assertSlotIsFree($technician, $start, $end);

        $visit = DB::transaction(function () use ($equipment, $technician, $start, $end, $data, $actor) {
            $visit = ScheduledVisit::create([
                'equipment_id' => $equipment->id,
                'site_id' => $equipment->site_id,
                'client_id' => $equipment->site->client_id,
                'technician_id' => $technician->id,
                'scheduled_start' => $start,
                'scheduled_end' => $end,
                'visit_type' => $data['visit_type'] ?? 'preventivo',
                // Explicito y no por el default de la tabla: recien creado el
                // modelo no lleva en memoria lo que puso la base, y
                // generateReminders mira el estado para decidir si avisa.
                'status' => 'programada',
                'notes' => $data['notes'] ?? null,
                'created_by' => $actor->id,
            ]);

            // Dentro de la transaccion: una visita sin recordatorios es una visita
            // de la que nadie se entera.
            $this->generateReminders($visit);

            return $visit;
        });

        $this->notifyParties($visit, new VisitScheduledNotification($visit));

        return $visit;
    }

    /**
     * Mueve una visita (edicion del modal o drag & drop del calendario). Revalida
     * siempre: arrastrar tambien puede dejarla en el descanso o en sabado.
     *
     * @param  bool  $force  aprobar sobre un conflicto conocido; manda coordinacion
     * @param  bool  $notify  false cuando quien llama manda su propio aviso del cambio
     *
     * @throws ValidationException
     */
    public function reschedule(
        ScheduledVisit $visit,
        CarbonImmutable $start,
        CarbonImmutable $end,
        ?User $technician = null,
        bool $force = false,
        bool $notify = true,
    ): ScheduledVisit {
        $technician ??= $visit->technician;

        // El unico caso que salta la validacion es una aprobacion forzada desde la
        // bandeja, y ahi la advertencia ya se dio en la interfaz.
        if (! $force) {
            $this->assertSlotIsFree($technician, $start, $end, $visit->id);
        }

        $previousStart = CarbonImmutable::parse($visit->scheduled_start);
        $previousEnd = CarbonImmutable::parse($visit->scheduled_end);
        $previousTechnicianId = (int) $visit->technician_id;

        DB::transaction(function () use ($visit, $technician, $start, $end) {
            $visit->update([
                'technician_id' => $technician->id,
                'scheduled_start' => $start,
                'scheduled_end' => $end,
            ]);

            $this->regenerateReminders($visit->refresh());
        });

        $visit->refresh();

        // Al aprobar una solicitud el aviso lo manda RescheduleRequestService, con
        // el "aprobamos tu cambio" y el antes/ahora en el mismo correo. Dos correos
        // en el mismo segundo diciendo lo mismo hacen que se dejen de leer los dos.
        if ($notify) {
            // El tecnico anterior tambien se entera: tenia la visita en su agenda.
            $extra = $previousTechnicianId !== (int) $visit->technician_id
                ? User::find($previousTechnicianId)
                : null;

            $this->notifyParties(
                $visit,
                new VisitRescheduledNotification($visit, $previousStart, $previousEnd),
                $extra ? [$extra] : [],
            );
        }

        return $visit;
    }

    public function cancel(ScheduledVisit $visit, ?string $reason = null): ScheduledVisit
    {
        DB::transaction(function () use ($visit, $reason) {
            $visit->update([
                'status' => 'cancelada',
                'cancelled_at' => now(),
                'cancel_reason' => $reason,
            ]);

            $this->obsoleteReminders($visit);
            $this->closeRescheduleRequests($visit, 'La visita se cancelo.');
        });

        $visit->refresh();

        $this->notifyParties($visit, new VisitCancelledNotification($visit));

        return $visit;
    }

    /**
     * Avisa a las partes de un cambio en la visita: el cliente y el tecnico.
     *
     * Coordinacion queda fuera a proposito — son quienes hacen el cambio y ya lo
     * ven en el tablero; llenarles la campana de sus propias acciones solo hace
     * que dejen de mirarla.
     *
     * Publico para que RescheduleRequestService avise a las mismas partes al
     * resolver una solicitud: repetir la regla en dos sitios acabaria con un
     * destinatario recibiendo la mitad de los avisos.
     *
     * @param  array<int, User>  $extraRecipients
     */
    public function notifyParties(ScheduledVisit $visit, Notification $notification, array $extraRecipients = []): void
    {
        $users = collect($this->reminderRecipients($visit))
            ->map(fn (array $pair) => $pair[0])
            ->concat($extraRecipients)
            ->filter()
            ->unique('id');

        if ($users->isNotEmpty()) {
            NotificationFacade::send($users, $notification);
        }
    }

    // ── Recordatorios ──

    /**
     * Materializa los recordatorios de una visita: una fila por destinatario y
     * momento, con la hora exacta ya calculada.
     *
     * Se hace aqui y no al vuelo cada cinco minutos para que el comando del
     * scheduler sea una consulta por indice, y para que quede rastro de a quien se
     * le avisó y cuándo.
     *
     * @return int filas creadas
     */
    public function generateReminders(ScheduledVisit $visit): int
    {
        // Una visita que ya no va a ocurrir no avisa a nadie.
        if (! in_array($visit->status, ScheduledVisit::BLOCKING_STATUSES, true)) {
            return 0;
        }

        $start = CarbonImmutable::parse($visit->scheduled_start);
        $created = 0;

        foreach ($this->reminderRecipients($visit) as [$user, $role]) {
            foreach ($this->offsetsFor($user, $role) as $offset) {
                $sendAt = $this->reminderSendAt($start, $offset);

                // Un aviso con fecha pasada no se crea: nadie quiere recibir el
                // "faltan 7 dias" de una visita que es pasado mañana.
                if (! $sendAt || $sendAt->isPast()) {
                    continue;
                }

                // Puede existir ya de una generacion anterior (reprogramar dentro
                // del mismo dia repite algun momento). Si ya se envio, no se
                // vuelve a mandar.
                $exists = VisitReminder::query()
                    ->where('scheduled_visit_id', $visit->id)
                    ->where('user_id', $user->id)
                    ->where('send_at', $sendAt)
                    ->whereIn('status', ['pendiente', 'enviado'])
                    ->exists();

                if ($exists) {
                    continue;
                }

                VisitReminder::create([
                    'scheduled_visit_id' => $visit->id,
                    'user_id' => $user->id,
                    'send_at' => $sendAt,
                    'channels' => $this->reminderChannels($user),
                ]);

                $created++;
            }
        }

        return $created;
    }

    /**
     * Invalida lo pendiente y vuelve a materializar. Se llama al mover la visita:
     * los avisos viejos apuntan a una fecha que ya no existe.
     */
    public function regenerateReminders(ScheduledVisit $visit): int
    {
        $this->obsoleteReminders($visit);

        return $this->generateReminders($visit);
    }

    /**
     * Rehace los avisos de un usuario para sus visitas futuras. Se llama al
     * cambiar sus preferencias: sin esto lo ya materializado seguiria con los
     * momentos viejos y el cambio no se notaria hasta la visita siguiente.
     *
     * @return int visitas afectadas
     */
    public function regenerateUpcomingRemindersFor(User $user): int
    {
        $visits = ScheduledVisit::query()
            ->blocking()
            ->where('scheduled_start', '>', now())
            ->where(function ($q) use ($user) {
                $q->where('technician_id', $user->id);

                if ($user->client_id) {
                    $q->orWhere('client_id', $user->client_id);
                }
            })
            ->get();

        foreach ($visits as $visit) {
            VisitReminder::query()
                ->where('scheduled_visit_id', $visit->id)
                ->where('user_id', $user->id)
                ->pending()
                ->update(['status' => 'obsoleto']);

            // generateReminders salta lo que ya existe para los demas
            // destinatarios, asi que rehace solo lo de este usuario.
            $this->generateReminders($visit);
        }

        return $visits->count();
    }

    /** Cancelar o completar una visita deja sus avisos sin sentido. */
    public function obsoleteReminders(ScheduledVisit $visit): void
    {
        VisitReminder::query()
            ->where('scheduled_visit_id', $visit->id)
            ->pending()
            ->update(['status' => 'obsoleto']);
    }

    /**
     * Quien recibe avisos de una visita: los usuarios del cliente y el tecnico.
     * Coordinacion no: ellos viven en el tablero.
     *
     * @return array<int, array{0: User, 1: string}> pares [usuario, rol]
     */
    private function reminderRecipients(ScheduledVisit $visit): array
    {
        $recipients = [];

        foreach (User::query()->where('client_id', $visit->client_id)->where('active', true)->get() as $admin) {
            $recipients[] = [$admin, 'admin'];
        }

        if ($visit->technician_id && $technician = User::find($visit->technician_id)) {
            $recipients[] = [$technician, 'technician'];
        }

        return $recipients;
    }

    /**
     * Los momentos de un usuario: los suyos si los configuro, si no los globales
     * de su rol. Una preferencia desactivada significa "no me avises".
     *
     * @return array<int, array{days_before: int, time: string}>
     */
    public function offsetsFor(User $user, string $role): array
    {
        $own = VisitReminderSetting::query()->where('user_id', $user->id)->first();

        if ($own) {
            return $own->enabled ? ($own->offsets ?: []) : [];
        }

        return ScheduleSetting::get(
            $role === 'technician' ? 'default_technician_offsets' : 'default_admin_offsets'
        ) ?: [];
    }

    /**
     * days_before dias antes del dia de la visita, a la hora indicada.
     * days_before = 0 es el mismo dia (el aviso matinal del tecnico).
     */
    private function reminderSendAt(CarbonImmutable $visitStart, array $offset): ?CarbonImmutable
    {
        $days = $offset['days_before'] ?? null;
        $time = $offset['time'] ?? null;

        if ($days === null || ! $time) {
            return null;
        }

        [$h, $m] = array_pad(explode(':', $time), 2, '0');

        return $visitStart->startOfDay()->subDays((int) $days)->setTime((int) $h, (int) $m);
    }

    /**
     * WhatsApp entra en la fase 5, cuando Meta apruebe las plantillas: mandarlo
     * antes es un envio que falla y una fila marcada como fallida.
     *
     * @return string[]
     */
    private function reminderChannels(User $user): array
    {
        return ['database', 'mail'];
    }

    // ── Ciclo de vida: la ejecucion mueve el estado de lo programado ──

    /**
     * El check-in del tecnico arranca la visita programada de ese equipo ese dia.
     *
     * Se busca por equipo + tecnico + dia y no por hora: el tecnico llega cuando
     * llega, y exigir que caiga dentro del rango dejaria en programada casi todas.
     */
    public function startVisitFor(int $equipmentId, int $technicianId, CarbonImmutable $when): ?ScheduledVisit
    {
        $visit = ScheduledVisit::query()
            ->where('equipment_id', $equipmentId)
            ->where('technician_id', $technicianId)
            ->whereDate('scheduled_start', $when->toDateString())
            ->whereIn('status', ['programada', 'reprogramacion_solicitada'])
            ->orderBy('scheduled_start')
            ->first();

        if ($visit) {
            $visit->update(['status' => 'en_curso']);
            // El tecnico ya esta en sitio: mover la fecha perdio sentido.
            $this->closeRescheduleRequests($visit, 'La visita se ejecuto en la fecha original.');
        }

        return $visit;
    }

    /**
     * Cierra las solicitudes de reprogramacion pendientes de una visita que dejo de
     * poder moverse (cancelada o ya arrancada).
     *
     * Es un update directo y no una llamada a RescheduleRequestService: ese servicio
     * ya depende de este, e inyectarlo aqui cerraria el circulo.
     */
    private function closeRescheduleRequests(ScheduledVisit $visit, string $notes): void
    {
        RescheduleRequest::query()
            ->where('scheduled_visit_id', $visit->id)
            ->pending()
            ->update([
                'status' => RescheduleRequest::RECHAZADA,
                'resolved_at' => now(),
                'resolution_notes' => $notes,
            ]);
    }

    /**
     * Enlaza la visita programada con la ejecutada copiandole el visit_uuid del
     * reporte. Es lo que permite cerrarla despues con una sola firma en lote.
     */
    public function linkVisitUuid(ServiceReport $report): void
    {
        if (! $report->visit_uuid || ! $report->technician_id) {
            return;
        }

        $this->matchingVisits($report)
            ->whereNull('visit_uuid')
            ->update(['visit_uuid' => $report->visit_uuid]);
    }

    /**
     * Cierra las visitas de los reportes firmados por el cliente.
     *
     * Una firma en lote cubre varios equipos de la misma sede, y cada equipo tiene
     * su propia visita programada: por eso se resuelve reporte a reporte y no por
     * el visit_uuid a secas.
     *
     * @param  iterable<int, ServiceReport>  $reports
     * @return int visitas cerradas
     */
    public function completeFromReports(iterable $reports): int
    {
        $completed = 0;

        foreach ($reports as $report) {
            $completed += $this->matchingVisits($report)->update(['status' => 'completada']);
        }

        return $completed;
    }

    /**
     * Visitas activas que corresponden a un reporte: la que ya comparte visit_uuid
     * o, si nunca se enlazo, la del mismo equipo, tecnico y dia.
     */
    private function matchingVisits(ServiceReport $report): \Illuminate\Database\Eloquent\Builder
    {
        return ScheduledVisit::query()
            ->blocking()
            ->where(function ($q) use ($report) {
                if ($report->visit_uuid) {
                    $q->orWhere('visit_uuid', $report->visit_uuid);
                }

                $q->orWhere(fn ($sub) => $sub
                    ->where('equipment_id', $report->equipment_id)
                    ->where('technician_id', $report->technician_id)
                    ->whereDate('scheduled_start', CarbonImmutable::parse($report->service_date)->toDateString()));
            });
    }

    /**
     * Valida que la cita quepa: dentro de la jornada del tecnico, en dia laborable,
     * sin pisar el descanso y sin solaparse con otra visita suya.
     *
     * @param  int|null  $ignoreVisitId  visita que se esta editando (no cuenta como solape)
     *
     * @throws ValidationException
     */
    public function assertSlotIsFree(
        User $technician,
        CarbonImmutable $start,
        CarbonImmutable $end,
        ?int $ignoreVisitId = null,
    ): void {
        $errors = array_merge(
            $this->workingHoursErrors($technician, $start, $end),
            $this->overlapErrors($technician, $start, $end, $ignoreVisitId),
        );

        if ($errors) {
            throw ValidationException::withMessages(['scheduled_start' => $errors]);
        }
    }

    /**
     * @return string[]
     */
    private function workingHoursErrors(User $technician, CarbonImmutable $start, CarbonImmutable $end): array
    {
        if ($end <= $start) {
            return ['La hora de fin debe ser posterior a la de inicio.'];
        }

        // Una visita que cruza la medianoche no cabe en ninguna jornada; ademas
        // romperia las comparaciones de hora de abajo, que asumen un mismo dia.
        if (! $start->isSameDay($end)) {
            return ['La visita debe empezar y terminar el mismo dia.'];
        }

        // Con la fecha: si ese dia tiene excepcion (festivo, vacaciones, un sabado
        // habilitado) manda la excepcion, no la jornada habitual.
        $window = $this->workingWindowFor($technician, $start);
        $errors = [];

        if (! in_array($start->dayOfWeekIso, $window['days'], true)) {
            $errors[] = $window['exception']
                ? sprintf('El %s no se trabaja%s.',
                    $start->format('d/m/Y'),
                    $window['exception']['note'] ? " ({$window['exception']['note']})" : '')
                : sprintf('%s no es un dia laborable para %s.',
                    $this->dayName($start->dayOfWeekIso), $technician->name);
        }

        $dayStart = $this->atTime($start, $window['start']);
        $dayEnd = $this->atTime($start, $window['end']);

        if ($start < $dayStart || $end > $dayEnd) {
            $errors[] = sprintf('La jornada de %s es de %s a %s.',
                $technician->name, $window['start'], $window['end']);
        }

        if ($window['break_start'] && $window['break_end']) {
            $breakStart = $this->atTime($start, $window['break_start']);
            $breakEnd = $this->atTime($start, $window['break_end']);

            // Cruce de rangos: basta con que se toquen para invadir el descanso.
            if ($start < $breakEnd && $end > $breakStart) {
                $errors[] = sprintf('La visita cae en el descanso (%s a %s).',
                    $window['break_start'], $window['break_end']);
            }
        }

        return $errors;
    }

    /**
     * @return string[]
     */
    private function overlapErrors(
        User $technician,
        CarbonImmutable $start,
        CarbonImmutable $end,
        ?int $ignoreVisitId,
    ): array {
        $clashes = $this->overlapping($technician, $start, $end, $ignoreVisitId);

        if ($clashes->isEmpty()) {
            return [];
        }

        return $clashes->map(fn (ScheduledVisit $v) => sprintf(
            'Se cruza con %s (%s a %s).',
            $v->equipment?->internal_code ?? 'otra visita',
            $v->scheduled_start->format('H:i'),
            $v->scheduled_end->format('H:i'),
        ))->all();
    }

    /**
     * Visitas del tecnico que pisan el rango dado.
     *
     * @return Collection<int, ScheduledVisit>
     */
    public function overlapping(
        User $technician,
        CarbonImmutable $start,
        CarbonImmutable $end,
        ?int $ignoreVisitId = null,
    ): Collection {
        return ScheduledVisit::query()
            ->with('equipment:id,internal_code')
            ->forTechnician($technician->id)
            ->blocking()
            ->between($start, $end)
            ->when($ignoreVisitId, fn ($q) => $q->whereKeyNot($ignoreVisitId))
            ->orderBy('scheduled_start')
            ->get();
    }

    /** Combina la fecha de $day con una hora "HH:MM". */
    private function atTime(CarbonImmutable $day, string $time): CarbonImmutable
    {
        [$h, $m] = array_pad(explode(':', $time), 2, '0');

        return $day->setTime((int) $h, (int) $m);
    }

    /** Las columnas `time` vuelven como "13:00:00"; la jornada se maneja en "HH:MM". */
    private function normalizeTime(?string $time): ?string
    {
        return $time ? substr($time, 0, 5) : null;
    }

    private function dayName(int $isoDay): string
    {
        return [
            1 => 'Lunes', 2 => 'Martes', 3 => 'Miercoles', 4 => 'Jueves',
            5 => 'Viernes', 6 => 'Sabado', 7 => 'Domingo',
        ][$isoDay] ?? "Dia $isoDay";
    }
}
