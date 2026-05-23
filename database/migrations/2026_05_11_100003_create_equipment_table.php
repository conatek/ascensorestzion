<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('equipment', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained()->cascadeOnDelete();
            $table->string('internal_code', 50)->unique();
            $table->string('customer_code', 50)->nullable();
            $table->enum('equipment_type', ['ascensor', 'escalera_electrica', 'montacargas', 'otro']);
            $table->string('brand', 100)->nullable();
            $table->string('model', 100)->nullable();
            $table->string('serial_number', 100)->nullable();
            $table->unsignedInteger('capacity_kg')->nullable();
            $table->unsignedSmallInteger('capacity_persons')->nullable();
            $table->unsignedSmallInteger('stops')->nullable();
            $table->decimal('speed_mps', 5, 2)->nullable();
            $table->date('installation_date')->nullable();
            $table->date('commissioning_date')->nullable();
            $table->enum('contract_type', ['mantenimiento', 'garantia', 'por_llamada', 'integral'])->nullable();
            $table->date('contract_start')->nullable();
            $table->date('contract_end')->nullable();
            $table->unsignedInteger('maintenance_frequency_days')->default(30);
            $table->string('photo_path')->nullable();
            $table->string('qr_code_path')->nullable();
            $table->enum('status', ['activo', 'fuera_servicio', 'modernizacion', 'retirado'])->default('activo');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['site_id', 'status']);
            $table->index('equipment_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('equipment');
    }
};
