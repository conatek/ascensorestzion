<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Cache en base de datos.
 *
 * La de ficheros no sirve en este servidor: la escriben dos usuarios distintos
 * —www-data desde la web y ubuntu desde el cron— y los directorios que crea uno
 * quedan cerrados para el otro. Eso hacia que `Cache::add` lanzara un TypeError
 * desde el cron, que es lo que rompio `withoutOverlapping` y con el el
 * `schedule:run` entero.
 *
 * Es el esquema estandar de Laravel: `cache` para los valores y `cache_locks`
 * para los candados atomicos.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cache', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->mediumText('value');
            $table->integer('expiration');
        });

        Schema::create('cache_locks', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->string('owner');
            $table->integer('expiration');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cache');
        Schema::dropIfExists('cache_locks');
    }
};
