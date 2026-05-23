<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_report_initial_conditions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_report_id')->constrained()->cascadeOnDelete();
            $table->string('condition_key', 50);
            $table->enum('value', ['si', 'no', 'na'])->default('na');
            $table->text('observation')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_report_initial_conditions');
    }
};
