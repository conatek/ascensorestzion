<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Cuando quiere cada usuario que le avisen. Sin fila valen los defaults globales
 * de schedule_settings, distintos para cliente y para tecnico.
 *
 * El canal de WhatsApp NO vive aqui: esta en users.notification_preferences, para
 * que el opt-in valga para todas las notificaciones y no solo para estas.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visit_reminder_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->boolean('enabled')->default(true);
            // Hasta 3: [{"days_before":7,"time":"08:00"}, ...]
            $table->json('offsets');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visit_reminder_settings');
    }
};
