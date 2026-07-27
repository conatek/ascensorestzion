<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Nivel intermedio de la cascada de duracion: visita (rango explicito) -> equipo
 * (esta columna) -> global (schedule_settings.default_duration_minutes = 90).
 *
 * Un montacargas de 12 paradas no se atiende en el mismo tiempo que un ascensor
 * residencial. Nullable = este equipo no tiene nada especial, usa el global.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('equipment', function (Blueprint $table) {
            $table->unsignedSmallInteger('default_visit_duration_minutes')
                ->nullable()
                ->after('maintenance_frequency_days');
        });
    }

    public function down(): void
    {
        Schema::table('equipment', function (Blueprint $table) {
            $table->dropColumn('default_visit_duration_minutes');
        });
    }
};
