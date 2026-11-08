<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Bloqueo de llegada en preventivos: el check-in queda ligado a la visita que lo
 * habilito y guarda como se decidio el ingreso, para que el master revise los
 * registros hechos sin conexion que no se pudieron verificar.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('technician_checkins', function (Blueprint $table) {
            $table->foreignId('scheduled_visit_id')->nullable()->after('equipment_id')
                ->constrained('scheduled_visits')->nullOnDelete();
            // a_tiempo | emergencia | desbloqueado | anticipado | sin_programar | reloj_inconsistente
            $table->string('access_status', 20)->nullable()->after('method');
            // Lo que decidio el telefono sin conexion: permitido | sin_agenda
            $table->string('offline_check', 20)->nullable()->after('access_status');
            $table->boolean('needs_review')->default(false)->after('offline_check');
        });

        Schema::table('scheduled_visits', function (Blueprint $table) {
            // Hora en que coordinacion recibio la llamada del cliente (correctivos)
            $table->dateTime('call_received_at')->nullable()->after('visit_type');
        });

        Schema::create('equipment_access_unlocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('equipment_id')->constrained('equipment')->cascadeOnDelete();
            // Sin cast date en el modelo: se guarda y se compara como 'Y-m-d'
            $table->date('unlock_date');
            $table->foreignId('unlocked_by')->constrained('users');
            $table->string('reason', 255)->nullable();
            $table->timestamps();

            $table->unique(['equipment_id', 'unlock_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('equipment_access_unlocks');

        Schema::table('scheduled_visits', function (Blueprint $table) {
            $table->dropColumn('call_received_at');
        });

        Schema::table('technician_checkins', function (Blueprint $table) {
            $table->dropConstrainedForeignId('scheduled_visit_id');
            $table->dropColumn(['access_status', 'offline_check', 'needs_review']);
        });
    }
};
