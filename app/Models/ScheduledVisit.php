<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

/**
 * Visita de mantenimiento programada.
 *
 * Se enlaza con la visita ejecutada por visit_uuid, el mismo agrupador que usa la
 * firma diferida en service_reports: el check-in la pasa a en_curso y el cierre de
 * los reportes a completada.
 */
class ScheduledVisit extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid',
        'equipment_id',
        'site_id',
        'client_id',
        'technician_id',
        'scheduled_start',
        'scheduled_end',
        'visit_type',
        'status',
        'visit_uuid',
        'notes',
        'created_by',
        'cancelled_at',
        'cancel_reason',
    ];

    protected $casts = [
        'scheduled_start' => 'datetime',
        'scheduled_end' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    /**
     * Estados que ocupan la agenda del tecnico: son los unicos que cuentan para
     * detectar solapes y los unicos que se pintan como ocupado en disponibilidad.
     */
    public const BLOCKING_STATUSES = ['programada', 'reprogramacion_solicitada', 'en_curso'];

    protected static function booted(): void
    {
        static::creating(function (self $visit) {
            $visit->uuid ??= (string) Str::uuid();
        });
    }

    public function equipment()
    {
        return $this->belongsTo(Equipment::class);
    }

    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function technician()
    {
        return $this->belongsTo(User::class, 'technician_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function reminders()
    {
        return $this->hasMany(VisitReminder::class);
    }

    /**
     * Reportes de la visita ejecutada. No es una FK: los une el visit_uuid que
     * comparten, y solo existe una vez que el tecnico hizo check-in.
     */
    public function serviceReports()
    {
        return $this->hasMany(ServiceReport::class, 'visit_uuid', 'visit_uuid');
    }

    // ── Scopes ──

    /** Visitas que se cruzan con el rango dado (no las contenidas en el). */
    public function scopeBetween(Builder $query, $from, $to): Builder
    {
        return $query->where('scheduled_start', '<', $to)
            ->where('scheduled_end', '>', $from);
    }

    public function scopeForTechnician(Builder $query, int $technicianId): Builder
    {
        return $query->where('technician_id', $technicianId);
    }

    public function scopeForClient(Builder $query, int $clientId): Builder
    {
        return $query->where('client_id', $clientId);
    }

    /** Las que ocupan agenda: excluye completadas, canceladas y no realizadas. */
    public function scopeBlocking(Builder $query): Builder
    {
        return $query->whereIn('status', self::BLOCKING_STATUSES);
    }
}
