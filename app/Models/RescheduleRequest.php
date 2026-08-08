<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Solicitud del cliente para mover una visita.
 *
 * Mientras esta pendiente la visita queda en reprogramacion_solicitada, pero
 * conservando su fecha original: si coordinacion la rechaza no hay nada que
 * deshacer, y mientras tanto el hueco no se lo quita nadie.
 */
class RescheduleRequest extends Model
{
    public const PENDIENTE = 'pendiente';

    public const APROBADA = 'aprobada';

    public const RECHAZADA = 'rechazada';

    protected $fillable = [
        'scheduled_visit_id',
        'requested_by',
        'original_start',
        'proposed_start',
        'proposed_end',
        'reason',
        'status',
        'resolved_by',
        'resolved_at',
        'resolution_notes',
    ];

    protected $casts = [
        'original_start' => 'datetime',
        'proposed_start' => 'datetime',
        'proposed_end' => 'datetime',
        'resolved_at' => 'datetime',
    ];

    public function scheduledVisit(): BelongsTo
    {
        return $this->belongsTo(ScheduledVisit::class);
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function resolver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', self::PENDIENTE);
    }

    public function isPending(): bool
    {
        return $this->status === self::PENDIENTE;
    }

    /**
     * Cierra la solicitud. Unico sitio que escribe resolved_by/resolved_at, para
     * que ninguna via de cierre se deje la firma a medias.
     */
    public function resolve(string $status, ?User $by = null, ?string $notes = null): self
    {
        $this->update([
            'status' => $status,
            'resolved_by' => $by?->id,
            'resolved_at' => now(),
            'resolution_notes' => $notes,
        ]);

        return $this;
    }
}
