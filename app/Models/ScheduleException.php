<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Un dia que se sale de la jornada normal.
 *
 * Sin `user_id` aplica a todos (un festivo). Con `user_id` es de esa persona, y
 * gana sobre la general: si el 20 de julio la empresa no trabaja pero Juan sí
 * tiene turno de guardia, la fila de Juan manda.
 */
class ScheduleException extends Model
{
    protected $fillable = ['user_id', 'date', 'working_hours', 'note'];

    protected $casts = [
        'working_hours' => 'array',
    ];

    /**
     * Se guarda como "Y-m-d" pelado, no con el cast `date` de Eloquent.
     *
     * Ese cast escribe "2026-08-10 00:00:00", y entonces ni el whereBetween por
     * fechas ni el updateOrCreate encuentran nada en sqlite, donde la columna es
     * texto: "2026-08-10 00:00:00" no esta entre "2026-08-10" y "2026-08-10".
     */
    protected function date(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => CarbonImmutable::parse($value)->startOfDay(),
            set: fn ($value) => CarbonImmutable::parse($value)->toDateString(),
        );
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Las del tecnico mas las generales. */
    public function scopeApplyingTo(Builder $query, ?int $userId): Builder
    {
        return $query->where(fn ($q) => $q->whereNull('user_id')->orWhere('user_id', $userId));
    }

    public function scopeBetweenDates(Builder $query, CarbonImmutable $from, CarbonImmutable $to): Builder
    {
        return $query->whereBetween('date', [$from->toDateString(), $to->toDateString()]);
    }

    /** true = ese dia no se trabaja; false = se trabaja con un horario distinto. */
    public function isClosed(): bool
    {
        return empty($this->working_hours);
    }

    /**
     * Resuelve las excepciones de un rango a un mapa fecha => excepcion que aplica,
     * quedandose con la del tecnico cuando compite con una general.
     *
     * Se hace en una sola consulta a proposito: el calculo de disponibilidad
     * recorre treinta dias y preguntar por cada uno serian treinta viajes.
     *
     * @return array<string, ScheduleException>
     */
    public static function mapForRange(?int $userId, CarbonImmutable $from, CarbonImmutable $to): array
    {
        return static::query()
            ->applyingTo($userId)
            ->betweenDates($from, $to)
            // Las generales primero para que la del tecnico, al llegar despues,
            // pise la entrada del mapa.
            ->orderByRaw('user_id IS NULL DESC')
            ->get()
            ->reduce(function (array $map, self $exception) {
                $map[$exception->date->toDateString()] = $exception;

                return $map;
            }, []);
    }

    /** La que manda ese dia, o null si no hay ninguna. */
    public static function resolveFor(?int $userId, CarbonImmutable $date): ?self
    {
        return static::mapForRange($userId, $date, $date)[$date->toDateString()] ?? null;
    }
}
