<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('catalogs', function (Blueprint $table) {
            $table->id();
            $table->string('scope', 50);
            $table->string('category', 50);
            $table->string('group_key', 50)->nullable();
            $table->string('key', 80);
            $table->string('label');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->unique(['scope', 'category', 'key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('catalogs');
    }
};
