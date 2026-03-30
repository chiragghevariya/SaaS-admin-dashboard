<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Laravel\Cashier\Exceptions\IncompletePayment;

class BillingController extends Controller
{
    private function authorizeAdmin(): void
    {
        abort_unless(
            auth()->user()?->hasAnyRole(['admin', 'super_admin']),
            403,
            'Only administrators can manage billing.'
        );
    }

    public function plans()
    {
        $this->authorizeAdmin();
        $tenant = app('tenant');

        $subscription = $tenant->subscription('default');

        return Inertia::render('Billing/Plans', [
            'current_plan'     => $subscription?->stripe_price,
            'subscription_status' => $subscription?->stripe_status,
            'plans' => [
                [
                    'name'        => 'Starter',
                    'price'       => 29,
                    'price_id'    => config('cashier.prices.starter'),
                    'features'    => ['Up to 5 users', 'Basic reports', 'Email support'],
                    'user_limit'  => 5,
                ],
                [
                    'name'        => 'Pro',
                    'price'       => 79,
                    'price_id'    => config('cashier.prices.pro'),
                    'features'    => ['Up to 25 users', 'Advanced analytics', 'Priority support'],
                    'user_limit'  => 25,
                    'highlighted' => true,
                ],
                [
                    'name'        => 'Enterprise',
                    'price'       => 199,
                    'price_id'    => config('cashier.prices.enterprise'),
                    'features'    => ['Unlimited users', 'Custom integrations', 'SLA guarantee'],
                    'user_limit'  => null,
                ],
            ],
        ]);
    }

    public function checkout(Request $request)
    {
        $this->authorizeAdmin();
        $request->validate(['price_id' => 'required|string']);

        $tenant = app('tenant');

        $checkout = $tenant->newSubscription('default', $request->price_id)
            ->checkout([
                'success_url' => route('billing.success') . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url'  => route('billing.plans'),
            ]);

        return Inertia::location($checkout->url);
    }

    public function success(Request $request)
    {
        $this->authorizeAdmin();
        return redirect()->route('billing.portal')
            ->with('success', 'Subscription activated! Welcome aboard.');
    }

    public function portal(Request $request)
    {
        $this->authorizeAdmin();
        $tenant = app('tenant');
        $subscription = $tenant->subscription('default');

        return Inertia::render('Billing/Portal', [
            'subscription' => $subscription ? [
                'status'        => $subscription->stripe_status,
                'price'         => $subscription->stripe_price,
                'trial_ends_at' => $subscription->trial_ends_at?->format('M d, Y'),
                'ends_at'       => $subscription->ends_at?->format('M d, Y'),
                'plan_name'     => $this->getPlanName($subscription->stripe_price),
            ] : null,
            'payment_method' => [
                'type'         => $tenant->pm_type,
                'last_four'    => $tenant->pm_last_four,
            ],
        ]);
    }

    public function redirectToPortal(Request $request)
    {
        $this->authorizeAdmin();
        $tenant = app('tenant');

        return $tenant->redirectToBillingPortal(route('billing.portal'));
    }

    public function cancel(Request $request)
    {
        $this->authorizeAdmin();
        $tenant = app('tenant');
        $tenant->subscription('default')?->cancel();

        return redirect()->route('billing.portal')
            ->with('success', 'Subscription cancelled. You will retain access until the period ends.');
    }

    private function getPlanName(?string $priceId): string
    {
        return match($priceId) {
            config('cashier.prices.starter')    => 'Starter',
            config('cashier.prices.pro')        => 'Pro',
            config('cashier.prices.enterprise') => 'Enterprise',
            default                             => 'Unknown Plan',
        };
    }
}
