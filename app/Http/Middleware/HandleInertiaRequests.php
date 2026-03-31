<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        $user = $request->user();

        // Resolve tenant safely — not available on unauthenticated/non-tenant routes
        $tenant = null;
        if ($user && app()->bound('tenant')) {
            try {
                $tenant = app('tenant');
            } catch (\Throwable) {
                $tenant = null;
            }
        }

        $plan = null;
        if ($tenant) {
            $planName = $tenant->activePlan();
            $plan = [
                'name'        => $planName,
                'subscribed'  => $tenant->subscribed('default'),
                'user_limit'  => $tenant->userLimit(),
                'user_count'  => $tenant->users()->count(),
                'features'    => [
                    'advanced_analytics'  => in_array($planName, ['pro', 'enterprise'], true),
                    'priority_support'    => in_array($planName, ['pro', 'enterprise'], true),
                    'custom_integrations' => $planName === 'enterprise',
                    'sla_guarantee'       => $planName === 'enterprise',
                ],
            ];
        }

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user ? [
                    'id'          => $user->id,
                    'name'        => $user->name,
                    'email'       => $user->email,
                    'status'      => $user->status,
                    'tenant_id'   => $user->tenant_id,
                    'roles'       => $user->getRoleNames(),
                    'permissions' => $user->getAllPermissions()->pluck('name'),
                ] : null,
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error'   => fn () => $request->session()->get('error'),
            ],
            'plan' => $plan,
        ];
    }
}
