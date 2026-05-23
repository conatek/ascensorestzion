<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_report_rstp_month', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_report_id')->unique()->constrained()->cascadeOnDelete();
            $table->smallInteger('year')->unsigned();
            $table->tinyInteger('month')->unsigned();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_report_rstp_month');
    }
};
