<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Excepciones a la jornada por fecha: festivos, vacaciones y sabados sueltos.
 *
 * `user_id` nulo significa que aplica a TODOS los tecnicos — asi un festivo se
 * carga una vez al año y no dieciocho veces por cada tecnico. Con `user_id` es
 * cosa de esa persona (vacaciones, un turno distinto ese dia).
 *
 * `working_hours` nulo = no se trabaja ese dia. Con horario = se trabaja con ese,
 * aunque el dia caiga fuera de los laborables normales.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('schedule_exceptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->json('working_hours')->nullable();
            $table->string('note')->nullable();
            $table->timestamps();

            // La consulta de disponibilidad: todas las del rango que le aplican a
            // un tecnico (las suyas y las generales).
            $table->index(['date', 'user_id']);
        });
        // Sin unique en (user_id, date): MySQL considera distintos dos NULL, asi
        // que no evitaria duplicar un festivo general. La unicidad se garantiza
        // con updateOrCreate en el controller, que ademas es el comportamiento
        // util — volver a guardar el mismo dia lo corrige en vez de fallar.
    }

    public function down(): void
    {
        Schema::dropIfExists('schedule_exceptions');
    }
};
