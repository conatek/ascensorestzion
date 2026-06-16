<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_report_attachments', function (Blueprint $table) {
            // Los adjuntos de reporte pasan a alojarse en Cloudinary.
            $table->string('url', 500)->nullable()->after('media_type');
            $table->string('cloudinary_public_id', 255)->nullable()->after('url');
            $table->unsignedSmallInteger('duration')->nullable()->after('cloudinary_public_id'); // segundos (video)
            // file_path queda opcional (legacy disco local).
            $table->string('file_path', 500)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('service_report_attachments', function (Blueprint $table) {
            $table->dropColumn(['url', 'cloudinary_public_id', 'duration']);
            $table->string('file_path', 500)->nullable(false)->change();
        });
    }
};
