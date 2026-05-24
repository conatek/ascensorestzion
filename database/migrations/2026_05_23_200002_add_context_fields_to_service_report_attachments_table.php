<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_report_attachments', function (Blueprint $table) {
            $table->string('condition_key', 50)->nullable()->after('type');
            $table->string('activity_key', 50)->nullable()->after('condition_key');
            $table->string('group_key', 50)->nullable()->after('activity_key');
            $table->enum('media_type', ['foto', 'video'])->default('foto')->after('group_key');
        });
    }

    public function down(): void
    {
        Schema::table('service_report_attachments', function (Blueprint $table) {
            $table->dropColumn(['condition_key', 'activity_key', 'group_key', 'media_type']);
        });
    }
};
