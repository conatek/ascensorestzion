<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('plan_id')->constrained()->restrictOnDelete();
            $table->string('status')->default('trial'); // trial, active, past_due, cancelled, expired
            $table->string('payment_method')->nullable(); // payu, manual
            $table->string('payu_subscription_id')->nullable();
            $table->datetime('trial_ends_at')->nullable();
            $table->datetime('current_period_start');
            $table->datetime('current_period_end');
            $table->datetime('cancelled_at')->nullable();
            $table->timestamps();

            $table->index('company_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
