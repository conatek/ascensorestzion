<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE service_report_audit_log MODIFY COLUMN action ENUM('created','edited','edited_by_master','signed_tech','signed_customer','reopened','cancelled','confirmed_reception') NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE service_report_audit_log MODIFY COLUMN action ENUM('created','edited','signed_tech','signed_customer','reopened','cancelled') NOT NULL");
    }
};
