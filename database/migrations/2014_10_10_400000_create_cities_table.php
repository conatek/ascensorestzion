<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('cities', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->bigInteger('province_id')->unsigned();
            $table->string('dane_code');

            $table->foreign('province_id')->references('id')->on('provinces');
        });
    }

    public function down()
    {
        Schema::dropIfExists('cities');
    }
};
