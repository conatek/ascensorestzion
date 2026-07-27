<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Jornada propia de un tecnico. Es la excepcion, no la regla: sin fila, el tecnico
 * hereda la jornada global de schedule_settings.
 *
 * Cada columna nullable se resuelve por separado, asi que se puede sobrescribir
 * solo el horario y seguir heredando los dias. El merge lo hace
 * ScheduleService::workingWindowFor(), que es la unica fuente de verdad para la
 * validacion de solapes, el grid del calendario y la disponibilidad del portal.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('technician_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->boolean('enabled')->default(true);
            $table->json('working_days')->nullable();     // null -> hereda los globales
            $table->json('working_hours')->nullable();    // {"start":"08:00","end":"18:00"}
            $table->time('break_start')->nullable();      // ambos null -> sin descanso
            $table->time('break_end')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('technician_schedules');
    }
};
