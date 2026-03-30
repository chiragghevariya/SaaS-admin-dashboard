<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('revenue_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('month'); // e.g. "2024-01"
            $table->decimal('mrr', 10, 2)->default(0);
            $table->integer('new_customers')->default(0);
            $table->integer('churned_customers')->default(0);
            $table->timestamps();

            $table->unique(['tenant_id', 'month']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('revenue_snapshots');
    }
};
