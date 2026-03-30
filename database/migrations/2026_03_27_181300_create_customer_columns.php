<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Cashier columns live on tenants (billing is per-tenant)
        Schema::table('tenants', function (Blueprint $table) {
            $table->string('stripe_id')->nullable()->change();
            $table->string('pm_type')->nullable()->change();
            $table->string('pm_last_four', 4)->nullable()->change();
            $table->timestamp('trial_ends_at')->nullable()->change();
        });
    }

    public function down(): void
    {
        // Columns already part of tenants table
    }
};
