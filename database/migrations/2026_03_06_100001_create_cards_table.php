<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('first_name');
            $table->string('last_name');
            // slug único dentro de la empresa (no globalmente)
            $table->string('slug');
            $table->string('job_title')->nullable();
            $table->string('photo_path')->nullable();         // URL Cloudinary
            $table->string('mobile_phone')->nullable();
            $table->string('whatsapp')->nullable();
            $table->string('email')->nullable();
            $table->string('linkedin')->nullable();
            $table->string('whatsapp_message')->nullable();   // mensaje predeterminado
            $table->text('description')->nullable();          // perfil profesional
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // slug único por empresa
            $table->unique(['company_id', 'slug']);
            $table->index('company_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cards');
    }
};
