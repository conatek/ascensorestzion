<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * La cita de mantenimiento.
 *
 * Se llama ScheduledVisit y no Visit porque visit_uuid ya significa otra cosa en el
 * sistema (el agrupador de reportes de una misma sede y dia para la firma diferida).
 * La visita programada se ENLAZA a la ejecutada por ese mismo visit_uuid: el check-in
 * del tecnico la pasa a en_curso y el cierre de los reportes a completada.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scheduled_visits', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();                     // para URLs del portal
            $table->foreignId('equipment_id')->constrained('equipment')->cascadeOnDelete();
            // site_id y client_id desnormalizados igual que en service_reports: evitan
            // el join Site->Client en cada query de calendario y sobreviven a que el
            // equipo cambie de sede.
            $table->foreignId('site_id')->constrained();
            $table->foreignId('client_id')->constrained();
            $table->foreignId('technician_id')->constrained('users');
            $table->dateTime('scheduled_start');
            $table->dateTime('scheduled_end');
            $table->enum('visit_type', ['preventivo', 'correctivo', 'especial'])->default('preventivo');
            $table->enum('status', [
                'programada', 'reprogramacion_solicitada', 'en_curso',
                'completada', 'no_realizada', 'cancelada',
            ])->default('programada');
            $table->uuid('visit_uuid')->nullable()->index();     // enlace con service_reports
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamp('cancelled_at')->nullable();
            $table->string('cancel_reason')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['technician_id', 'scheduled_start']);   // agenda del tecnico y solapes
            $table->index(['client_id', 'scheduled_start']);       // portal
            $table->index(['equipment_id', 'scheduled_start']);    // cronograma por equipo
            $table->index(['status', 'scheduled_start']);          // tableros
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scheduled_visits');
    }
};
