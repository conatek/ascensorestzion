<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_report_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_report_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['foto_antes', 'foto_despues', 'cotizacion', 'otro']);
            $table->string('file_path', 500);
            $table->string('original_name');
            $table->foreignId('uploaded_by')->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_report_attachments');
    }
};
