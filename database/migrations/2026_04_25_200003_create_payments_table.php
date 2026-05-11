<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subscription_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 10, 2);
            $table->string('currency', 10)->default('COP');
            $table->string('status')->default('pending'); // pending, approved, declined, refunded
            $table->string('payment_method')->nullable();  // credit_card, pse, cash, bank_transfer
            $table->string('payu_order_id')->nullable();
            $table->string('payu_transaction_id')->nullable();
            $table->string('payu_reference_code')->nullable();
            $table->string('response_code')->nullable();
            $table->datetime('paid_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index('company_id');
            $table->index('status');
            $table->index('payu_reference_code');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
