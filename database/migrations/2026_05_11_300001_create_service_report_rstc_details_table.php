<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_report_rstc_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_report_id')->unique()->constrained()->cascadeOnDelete();
            $table->dateTime('call_time')->nullable();
            $table->dateTime('entry_time')->nullable();
            $table->dateTime('exit_time')->nullable();
            $table->smallInteger('response_time_hh')->unsigned()->nullable();
            $table->smallInteger('response_time_mm')->unsigned()->nullable();
            $table->enum('fault_location', ['sistema_puertas', 'control_maniobras', 'maquinaria', 'recorrido', 'otra'])->nullable();
            $table->text('fault_location_other')->nullable();
            $table->enum('fault_cause', ['energia_externa', 'inundacion_humedad', 'tercero', 'tecnica_equipo', 'otra'])->nullable();
            $table->text('fault_cause_other')->nullable();
            $table->enum('fault_solution_area', ['control_maniobras', 'perifericos', 'puertas_piso', 'operador_puertas_cabina', 'activacion_interruptores', 'otra'])->nullable();
            $table->text('fault_solution_other')->nullable();
            $table->text('analysis_notes')->nullable();
            $table->text('cause_notes')->nullable();
            $table->text('solution_notes')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_report_rstc_details');
    }
};
