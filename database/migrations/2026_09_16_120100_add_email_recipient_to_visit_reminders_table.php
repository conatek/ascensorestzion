<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Un recordatorio puede ir ahora a un correo de notificación suelto (sin
        // usuario). Se hace user_id nullable y se añade email.
        Schema::table('visit_reminders', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });

        Schema::table('visit_reminders', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->change();
            $table->foreign('user_id')->references('id')->on('users');
            $table->string('email')->nullable()->after('user_id');
        });
    }

    public function down(): void
    {
        Schema::table('visit_reminders', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('email');
        });

        Schema::table('visit_reminders', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable(false)->change();
            $table->foreign('user_id')->references('id')->on('users');
        });
    }
};
