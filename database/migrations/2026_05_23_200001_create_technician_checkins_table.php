<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('technician_checkins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('equipment_id')->constrained('equipment')->restrictOnDelete();
            $table->foreignId('technician_id')->constrained('users')->restrictOnDelete();
            $table->dateTime('checked_in_at');
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->decimal('accuracy', 8, 2)->nullable();
            $table->enum('method', ['qr', 'manual'])->default('manual');
            $table->boolean('is_emergency')->default(false);
            $table->dateTime('call_received_at')->nullable();
            $table->unsignedInteger('response_time_minutes')->nullable();
            $table->foreignId('service_report_id')->nullable()->constrained('service_reports')->nullOnDelete();
            $table->timestamps();

            $table->index(['equipment_id', 'checked_in_at']);
            $table->index(['technician_id', 'checked_in_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('technician_checkins');
    }
};
