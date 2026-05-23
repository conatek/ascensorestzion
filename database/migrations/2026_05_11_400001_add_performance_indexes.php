<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_reports', function (Blueprint $table) {
            $table->index(['report_type', 'status']);
            $table->index(['client_id', 'report_type', 'service_date']);
        });

        Schema::table('service_report_fault_codes', function (Blueprint $table) {
            $table->index(['service_report_id', 'code']);
        });
    }

    public function down(): void
    {
        Schema::table('service_reports', function (Blueprint $table) {
            $table->dropIndex(['report_type', 'status']);
            $table->dropIndex(['client_id', 'report_type', 'service_date']);
        });

        Schema::table('service_report_fault_codes', function (Blueprint $table) {
            $table->dropIndex(['service_report_id', 'code']);
        });
    }
};
