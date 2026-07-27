<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * Configuracion global del cronograma en clave/valor JSON.
 *
 * Se lee en cada validacion de solape y en cada render del calendario, asi que va
 * cacheada. Cualquier escritura invalida el cache entero (son media docena de claves,
 * no compensa afinarlo mas).
 */
class ScheduleSetting extends Model
{
    protected $primaryKey = 'key';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = ['key', 'value'];

    protected $casts = ['value' => 'array'];

    private const CACHE_KEY = 'schedule_settings';

    /** Defaults de fabrica: lo que aplica si la tabla esta vacia o le falta una clave. */
    public const DEFAULTS = [
        'default_duration_minutes' => 90,
        'working_hours' => ['start' => '08:00', 'end' => '18:00'],
        'working_break' => ['start' => '13:00', 'end' => '14:00'],
        'working_days' => [1, 2, 3, 4, 5],   // lunes a viernes (ISO-8601)
        'default_admin_offsets' => [
            ['days_before' => 7, 'time' => '08:00'],
            ['days_before' => 3, 'time' => '08:00'],
            ['days_before' => 1, 'time' => '08:00'],
        ],
        'default_technician_offsets' => [
            ['days_before' => 1, 'time' => '18:00'],
            ['days_before' => 0, 'time' => '06:00'],
        ],
        'availability_slot_minutes' => 30,
        'travel_buffer_minutes' => 30,
        'min_reschedule_notice_hours' => 24,
    ];

    /**
     * Todas las claves resueltas: lo guardado sobre los defaults de fabrica.
     */
    public static function resolved(): array
    {
        return Cache::rememberForever(self::CACHE_KEY, function () {
            // pluck sobre el builder de Eloquent si aplica el cast 'array',
            // asi que los JSON llegan ya decodificados.
            $stored = static::query()->pluck('value', 'key')->all();

            return array_merge(self::DEFAULTS, $stored);
        });
    }

    public static function get(string $key, mixed $fallback = null): mixed
    {
        return self::resolved()[$key] ?? $fallback ?? self::DEFAULTS[$key] ?? null;
    }

    public static function put(string $key, mixed $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
        Cache::forget(self::CACHE_KEY);
    }

    public static function flushCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}
