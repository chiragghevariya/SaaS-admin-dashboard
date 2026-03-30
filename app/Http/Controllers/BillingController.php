<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
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
            'current_plan'        => $subscription?->stripe_price,
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
        $tenant       = app('tenant');
        $subscription = $tenant->subscription('default');

        $subscriptionData = null;

        if ($subscription) {
            $subscriptionData = [
                'status'          => $subscription->stripe_status,
                'plan_name'       => $this->getPlanName($subscription->stripe_price),
                'plan_price'      => $this->getPlanPrice($subscription->stripe_price),
                'trial_ends_at'   => $subscription->trial_ends_at?->format('M d, Y'),
                'ends_at'         => $subscription->ends_at?->format('M d, Y'),
                'next_billing_at' => $this->getNextBillingDate($subscription),
                'on_grace_period' => $subscription->onGracePeriod(),
                'cancelled'       => $subscription->canceled(),
            ];
        }

        // Prefer locally-cached pm_* columns; fall back to Stripe API if empty.
        $pmType     = $tenant->pm_type;
        $pmLastFour = $tenant->pm_last_four;

        if (! $pmLastFour && $tenant->stripe_id) {
            try {
                $method = $tenant->defaultPaymentMethod();
                if ($method) {
                    $pmType     = $method->card->brand ?? $method->type;
                    $pmLastFour = $method->card->last4 ?? null;
                }
            } catch (\Exception) {
                // Stripe unreachable — leave null.
            }
        }

        return Inertia::render('Billing/Portal', [
            'subscription'   => $subscriptionData,
            'payment_method' => [
                'type'      => $pmType,
                'last_four' => $pmLastFour,
            ],
        ]);
    }

    /**
     * Redirect to the Stripe Customer Portal.
     * Uses Inertia::location() so the external URL triggers a full browser redirect
     * instead of an Inertia page visit (which would fail with "failed to load data").
     */
    public function redirectToPortal(Request $request)
    {
        $this->authorizeAdmin();
        $tenant = app('tenant');

        $session = $tenant->stripe()->billingPortal->sessions->create([
            'customer'   => $tenant->stripe_id,
            'return_url' => route('billing.portal'),
        ]);

        return Inertia::location($session->url);
    }

    public function cancel(Request $request)
    {
        $this->authorizeAdmin();
        $tenant = app('tenant');
        $tenant->subscription('default')?->cancel();

        return redirect()->route('billing.portal')
            ->with('success', 'Subscription cancelled. You will retain access until the end of the billing period.');
    }

    public function resume(Request $request)
    {
        $this->authorizeAdmin();
        $tenant       = app('tenant');
        $subscription = $tenant->subscription('default');

        abort_unless($subscription?->onGracePeriod(), 422, 'This subscription cannot be resumed.');

        $subscription->resume();

        return redirect()->route('billing.portal')
            ->with('success', 'Subscription resumed. Auto-renew has been re-enabled.');
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    private function getPlanName(?string $priceId): string
    {
        return match ($priceId) {
            config('cashier.prices.starter')    => 'Starter',
            config('cashier.prices.pro')        => 'Pro',
            config('cashier.prices.enterprise') => 'Enterprise',
            default                             => 'Unknown Plan',
        };
    }

    private function getPlanPrice(?string $priceId): ?int
    {
        return match ($priceId) {
            config('cashier.prices.starter')    => 29,
            config('cashier.prices.pro')        => 79,
            config('cashier.prices.enterprise') => 199,
            default                             => null,
        };
    }

    private function getNextBillingDate($subscription): ?string
    {
        // On grace period means cancelled — no future billing
        if ($subscription->onGracePeriod()) {
            return null;
        }

        // Trial: next charge is at trial end
        if ($subscription->onTrial()) {
            return $subscription->trial_ends_at?->format('M d, Y');
        }

        // Fetch current period end from Stripe
        try {
            $stripeSub = $subscription->asStripeSubscription();
            $timestamp  = $stripeSub->current_period_end ?? null;

            return $timestamp ? Carbon::createFromTimestamp($timestamp)->format('M d, Y') : null;
        } catch (\Exception) {
            return null;
        }
    }
}
