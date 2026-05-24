<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Campos de confirmacion de recepcion en service_reports
        Schema::table('service_reports', function (Blueprint $table) {
            $table->string('reception_token', 64)->unique()->nullable()->after('final_pdf_path');
            $table->dateTime('reception_confirmed_at')->nullable()->after('reception_token');
            $table->string('reception_confirmed_ip', 45)->nullable()->after('reception_confirmed_at');
            $table->text('reception_confirmed_ua')->nullable()->after('reception_confirmed_ip');
        });

        // WhatsApp del contacto del cliente
        Schema::table('clients', function (Blueprint $table) {
            $table->string('whatsapp_phone', 20)->nullable()->after('contact_phone');
        });

        // Preferencias de notificacion del usuario
        Schema::table('users', function (Blueprint $table) {
            $table->json('notification_preferences')->nullable()->after('image_url');
        });
    }

    public function down(): void
    {
        Schema::table('service_reports', function (Blueprint $table) {
            $table->dropColumn(['reception_token', 'reception_confirmed_at', 'reception_confirmed_ip', 'reception_confirmed_ua']);
        });

        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn('whatsapp_phone');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('notification_preferences');
        });
    }
};
