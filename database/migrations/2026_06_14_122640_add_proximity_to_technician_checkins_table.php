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
        Schema::table('technician_checkins', function (Blueprint $table) {
            $table->unsignedInteger('distance_meters')->nullable()->after('accuracy');
            $table->boolean('within_radius')->nullable()->after('distance_meters');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('technician_checkins', function (Blueprint $table) {
            $table->dropColumn(['distance_meters', 'within_radius']);
        });
    }
};
