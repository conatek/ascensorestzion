<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Solicitudes de cambio de fecha que el cliente manda desde el portal.
 *
 * La tabla es tambien el historial: las resueltas se conservan para que el drawer
 * de la visita pueda contar quien pidio que, quien lo decidio y por que.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reschedule_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('scheduled_visit_id')->constrained()->cascadeOnDelete();
            $table->foreignId('requested_by')->constrained('users');
            // Instantanea de la fecha que tenia la visita al pedir el cambio: si
            // despues se mueve por otra via, el mensaje "del jueves 7 al martes 12"
            // tiene que seguir diciendo lo que el cliente vio.
            $table->dateTime('original_start');
            $table->dateTime('proposed_start');
            $table->dateTime('proposed_end');
            $table->text('reason')->nullable();
            $table->enum('status', ['pendiente', 'aprobada', 'rechazada'])->default('pendiente');
            $table->foreignId('resolved_by')->nullable()->constrained('users');
            $table->timestamp('resolved_at')->nullable();
            $table->text('resolution_notes')->nullable();
            $table->timestamps();

            // La bandeja de coordinacion.
            $table->index(['status', 'created_at']);
            // "¿esta visita ya tiene una pendiente?", en cada solicitud y en cada
            // payload del portal. No es unique: MySQL no tiene indices parciales,
            // asi que la regla de "una sola pendiente" se garantiza con un
            // lockForUpdate sobre la visita al crearla.
            $table->index(['scheduled_visit_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reschedule_requests');
    }
};
