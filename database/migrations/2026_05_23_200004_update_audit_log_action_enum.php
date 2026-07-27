<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * MODIFY COLUMN es sintaxis exclusiva de MySQL. En sqlite (la conexion de los
 * tests) la sentencia revienta y tumba toda la suite, asi que se omite: alli el
 * enum se crea con un CHECK y no hay nada que ampliar.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("ALTER TABLE service_report_audit_log MODIFY COLUMN action ENUM('created','edited','edited_by_master','signed_tech','signed_customer','reopened','cancelled','confirmed_reception') NOT NULL");
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("ALTER TABLE service_report_audit_log MODIFY COLUMN action ENUM('created','edited','signed_tech','signed_customer','reopened','cancelled') NOT NULL");
    }
};
