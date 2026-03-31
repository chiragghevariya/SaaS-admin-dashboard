<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Cashier\Billable;

class Tenant extends Model
{
    use HasFactory, Billable;

    protected $fillable = [
        'name',
        'slug',
        'logo',
    ];

    protected $casts = [
        'trial_ends_at' => 'datetime',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function revenueSnapshots()
    {
        return $this->hasMany(RevenueSnapshot::class)->orderBy('month');
    }

    public function apiKeys()
    {
        return $this->hasMany(\App\Models\ApiKey::class);
    }

    /**
     * Returns the slug of the active plan ('starter', 'pro', 'enterprise') or null.
     */
    public function activePlan(): ?string
    {
        $subscription = $this->subscription('default');

        if (! $subscription || ! $subscription->active()) {
            return null;
        }

        return match ($subscription->stripe_price) {
            config('cashier.prices.starter')    => 'starter',
            config('cashier.prices.pro')        => 'pro',
            config('cashier.prices.enterprise') => 'enterprise',
            default                             => null,
        };
    }

    /**
     * Check whether the tenant's plan includes a given feature.
     * Features: advanced_analytics, priority_support, custom_integrations, sla_guarantee
     */
    public function hasFeature(string $feature): bool
    {
        $plan = $this->activePlan();

        $map = [
            'advanced_analytics'  => ['pro', 'enterprise'],
            'priority_support'    => ['pro', 'enterprise'],
            'custom_integrations' => ['enterprise'],
            'sla_guarantee'       => ['enterprise'],
        ];

        return in_array($plan, $map[$feature] ?? [], true);
    }

    /**
     * Returns the maximum number of users allowed by the plan.
     * Returns null for unlimited (enterprise), 0 for no plan.
     */
    public function userLimit(): int|null
    {
        return match ($this->activePlan()) {
            'starter'    => 5,
            'pro'        => 25,
            'enterprise' => null,
            default      => 0, // no active plan
        };
    }
}
