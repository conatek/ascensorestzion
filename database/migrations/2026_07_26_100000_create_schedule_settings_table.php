<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Configuracion global del cronograma, en clave/valor JSON.
 *
 * Los defaults viven aqui y no en constantes para que coordinacion pueda ajustar
 * la jornada o la duracion sin un deploy. La jornada de un tecnico concreto se
 * sobrescribe en technician_schedules.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('schedule_settings', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->json('value');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('schedule_settings');
    }
};
