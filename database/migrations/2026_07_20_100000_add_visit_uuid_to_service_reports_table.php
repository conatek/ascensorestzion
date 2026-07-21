<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('service_reports', function (Blueprint $table) {
            // Agrupa los reportes de una misma visita (mismo tecnico + misma sede + mismo dia)
            // para permitir la firma diferida del cliente en lote.
            $table->uuid('visit_uuid')->nullable()->after('client_uuid');
            $table->index(['visit_uuid', 'status'], 'service_reports_visit_status_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('service_reports', function (Blueprint $table) {
            $table->dropIndex('service_reports_visit_status_idx');
            $table->dropColumn('visit_uuid');
        });
    }
};
