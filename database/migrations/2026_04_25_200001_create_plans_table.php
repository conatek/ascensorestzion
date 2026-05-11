<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();             // guest, basico, pro
            $table->string('display_name');                // Gratis, Básico, Pro
            $table->decimal('price_monthly', 10, 2)->default(0);
            $table->decimal('price_yearly', 10, 2)->default(0);
            $table->integer('max_cards')->default(1);
            $table->integer('max_products')->nullable();   // null = ilimitado
            $table->integer('max_services')->nullable();   // null = ilimitado
            $table->json('available_templates')->nullable(); // null = todas
            $table->boolean('show_watermark')->default(false);
            $table->json('features')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};
