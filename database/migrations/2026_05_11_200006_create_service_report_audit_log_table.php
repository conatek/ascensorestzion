<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_report_audit_log', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_report_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained();
            $table->enum('action', ['created', 'edited', 'signed_tech', 'signed_customer', 'reopened', 'cancelled']);
            $table->json('changes_json')->nullable();
            $table->string('ip', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_report_audit_log');
    }
};
