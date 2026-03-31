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

            // Evaluated lazily — session flash is only available after the controller runs
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error'   => fn () => $request->session()->get('error'),
            ],

            // Evaluated lazily — tenant middleware binds app('tenant') AFTER share() is called,
            // so this closure must be resolved at render time, not at share() time.
            'plan' => function () use ($user) {
                if (! $user || ! app()->bound('tenant')) {
                    return null;
                }

                try {
                    $tenant = app('tenant');
                } catch (\Throwable) {
                    return null;
                }

                $planName = $tenant->activePlan();

                return [
                    'name'       => $planName,
                    'subscribed' => $tenant->subscribed('default'),
                    'user_limit' => $tenant->userLimit(),
                    'user_count' => $tenant->users()->count(),
                    'features'   => [
                        'advanced_analytics'  => in_array($planName, ['pro', 'enterprise'], true),
                        'priority_support'    => in_array($planName, ['pro', 'enterprise'], true),
                        'custom_integrations' => $planName === 'enterprise',
                        'sla_guarantee'       => $planName === 'enterprise',
                    ],
                ];
            },
        ];
    }
}
