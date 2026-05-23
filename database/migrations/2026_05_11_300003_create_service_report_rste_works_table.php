<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_report_rste_works', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_report_id')->constrained()->cascadeOnDelete();
            $table->enum('group_key', ['cuarto_maquinas', 'cabina', 'pozo_foso']);
            $table->string('work_key', 50);
            $table->boolean('is_ok')->default(false);
            $table->text('observation')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_report_rste_works');
    }
};
