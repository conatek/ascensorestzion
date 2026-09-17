<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            // Lista de correos que reciben las notificaciones del cliente, separada
            // de los usuarios con acceso (login). Reemplaza al único contact_email.
            $table->json('notification_emails')->nullable()->after('contact_email');
        });

        // Backfill: el contact_email que hubiera pasa a ser el primer correo de la lista.
        foreach (DB::table('clients')->whereNotNull('contact_email')->where('contact_email', '!=', '')->get(['id', 'contact_email']) as $client) {
            DB::table('clients')->where('id', $client->id)->update([
                'notification_emails' => json_encode([$client->contact_email]),
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn('notification_emails');
        });
    }
};
