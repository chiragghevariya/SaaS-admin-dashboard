<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('stripe_invoice_id')->unique();
            $table->string('stripe_payment_intent_id')->nullable();
            $table->unsignedBigInteger('amount'); // in cents
            $table->string('currency', 3)->default('usd');
            $table->string('status')->default('succeeded');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_logs');
    }
};
