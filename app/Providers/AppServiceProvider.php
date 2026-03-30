<?php

namespace App\Providers;

use App\Models\User;
use App\Policies\UserPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Laravel\Cashier\Cashier;
use App\Models\Tenant;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);

        // Cashier uses Tenant as the billable model
        Cashier::useCustomerModel(Tenant::class);

        // Register policies
        Gate::policy(User::class, UserPolicy::class);
    }
}
