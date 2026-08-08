<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Recordatorios materializados: una fila por (visita × destinatario × momento).
 *
 * Se calculan al programar la visita en vez de resolverlos al vuelo cada cinco
 * minutos. Asi el comando del scheduler es una consulta por indice —
 * status + send_at— en lugar de recorrer todas las visitas futuras cruzandolas
 * con las preferencias de cada usuario, y ademas queda el rastro de que se
 * envio, cuando y a quien.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visit_reminders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('scheduled_visit_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained();
            $table->dateTime('send_at');
            $table->json('channels');
            $table->enum('status', ['pendiente', 'enviado', 'fallido', 'obsoleto'])->default('pendiente');
            $table->timestamp('sent_at')->nullable();
            $table->string('error')->nullable();
            $table->timestamps();

            // La consulta del comando, cada cinco minutos.
            $table->index(['status', 'send_at']);
            // Para regenerar tras una reprogramacion sin duplicar avisos. No es
            // unique a proposito: las filas obsoletas se conservan como rastro y
            // reprogramar dentro del mismo dia repite algun send_at.
            $table->index(['scheduled_visit_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visit_reminders');
    }
};
