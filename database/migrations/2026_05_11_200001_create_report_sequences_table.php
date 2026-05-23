<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('report_sequences', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['RSTP', 'RSTC', 'RSTE']);
            $table->smallInteger('year')->unsigned();
            $table->unsignedInteger('last_number')->default(0);
            $table->timestamps();

            $table->unique(['type', 'year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_sequences');
    }
};
