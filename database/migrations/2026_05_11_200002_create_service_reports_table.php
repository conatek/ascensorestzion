<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_reports', function (Blueprint $table) {
            $table->id();
            $table->string('report_number', 20)->unique();
            $table->enum('report_type', ['RSTP', 'RSTC', 'RSTE']);
            $table->foreignId('equipment_id')->constrained('equipment')->restrictOnDelete();
            $table->foreignId('client_id')->constrained()->restrictOnDelete();
            $table->foreignId('site_id')->constrained()->restrictOnDelete();
            $table->string('customer_order_ref', 100)->nullable();
            $table->date('service_date');
            $table->time('time_in')->nullable();
            $table->time('time_out')->nullable();
            $table->foreignId('technician_id')->constrained('users')->restrictOnDelete();
            $table->unsignedBigInteger('secondary_technician_id')->nullable();
            $table->boolean('equipment_functional')->nullable();
            $table->boolean('generates_quotation')->default(false);
            $table->boolean('requires_parts_change')->default(false);
            $table->text('conclusion_notes')->nullable();
            $table->string('technician_signature_path')->nullable();
            $table->dateTime('technician_signed_at')->nullable();
            $table->string('customer_signer_name')->nullable();
            $table->string('customer_signer_document', 50)->nullable();
            $table->string('customer_signature_path')->nullable();
            $table->dateTime('customer_signed_at')->nullable();
            $table->enum('status', ['borrador', 'firmado_tecnico', 'firmado_cliente', 'cerrado', 'anulado'])->default('borrador');
            $table->string('final_pdf_path')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->unsignedBigInteger('last_edited_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('secondary_technician_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('last_edited_by')->references('id')->on('users')->nullOnDelete();

            $table->index(['report_type', 'service_date']);
            $table->index(['equipment_id', 'service_date']);
            $table->index('technician_id');
            $table->index('client_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_reports');
    }
};
