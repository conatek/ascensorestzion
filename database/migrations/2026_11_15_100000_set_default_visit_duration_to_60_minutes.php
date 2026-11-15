<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Pedido de los socios (2026-11-15): la duracion por defecto de una visita pasa de
 * 90 a 60 minutos, para todos los tipos. Solo cambia la precarga de las visitas
 * nuevas: las ya programadas conservan su hora de fin.
 */
return new class extends Migration
{
    public function up(): void
    {
        $this->setDuration(60);
    }

    public function down(): void
    {
        $this->setDuration(90);
    }

    private function setDuration(int $minutes): void
    {
        DB::table('schedule_settings')->updateOrInsert(
            ['key' => 'default_duration_minutes'],
            ['value' => json_encode($minutes), 'updated_at' => now(), 'created_at' => now()],
        );

        // Misma clave que ScheduleSetting::CACHE_KEY
        Cache::forget('schedule_settings');
    }
};
