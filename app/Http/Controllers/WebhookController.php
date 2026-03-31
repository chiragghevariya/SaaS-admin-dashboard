<?php

namespace App\Http\Controllers;

use App\Mail\SubscriptionCancelled;
use App\Models\PaymentLog;
use App\Models\Tenant;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Laravel\Cashier\Http\Controllers\WebhookController as CashierWebhookController;
use Symfony\Component\HttpFoundation\Response;

class WebhookController extends CashierWebhookController
{
    /**
     * Handle invoice.payment_succeeded:
     * — log the payment to payment_logs
     * — recover past_due subscriptions to active
     */


    public function handleInvoicePaymentSucceeded(array $payload)
    {
        Log::info('Handling invoice.payment_succeeded webhook', [
            'invoice_id' => $payload['data']['object']['id'] ?? null,
            'customer_id' => $payload['data']['object']['customer'] ?? null,
        ]);

        // $response = parent::handleInvoicePaymentSucceeded($payload);
        $response = parent::handleCustomerSubscriptionCreated($payload);

        $invoice = $payload['data']['object'];
        $tenant  = Tenant::where('stripe_id', $invoice['customer'] ?? '')->first();

        if ($tenant && ($invoice['amount_paid'] ?? 0) > 0) {
            Log::info('Processing successful payment webhook', [
                'tenant_id' => $tenant->id,
                'invoice_id' => $invoice['id'],
            ]);

            PaymentLog::updateOrCreate(
                ['stripe_invoice_id' => $invoice['id']],
                [
                    'tenant_id'                => $tenant->id,
                    'stripe_payment_intent_id' => $invoice['payment_intent'] ?? null,
                    'amount'                   => $invoice['amount_paid'],
                    'currency'                 => strtolower($invoice['currency'] ?? 'usd'),
                    'status'                   => 'succeeded',
                ]
            );

            // Recover a past_due subscription when payment succeeds
            $subscription = $tenant->subscription('default');
            if ($subscription && $subscription->stripe_status === 'past_due') {
                $subscription->update(['stripe_status' => 'active']);
            }

            Log::info('Payment succeeded', [
                'tenant_id'  => $tenant->id,
                'invoice_id' => $invoice['id'],
                'amount'     => $invoice['amount_paid'],
                'currency'   => $invoice['currency'] ?? 'usd',
            ]);
        }

        return $response;
        // return true;
    }

    /**
     * Handle customer.subscription.deleted:
     * — let Cashier mark the subscription as cancelled in the DB
     * — email all admins of the affected tenant
     */
    public function handleCustomerSubscriptionDeleted(array $payload): Response
    {
        $response = parent::handleCustomerSubscriptionDeleted($payload);

        $stripeSubscription = $payload['data']['object'];
        $tenant = Tenant::where('stripe_id', $stripeSubscription['customer'] ?? '')->first();

        if ($tenant) {
            $admins = $tenant->users()
                ->whereHas('roles', fn ($q) => $q->whereIn('name', ['admin', 'super_admin']))
                ->get();

            foreach ($admins as $admin) {
                Mail::to($admin->email)->queue(new SubscriptionCancelled($tenant));
            }

            Log::info('Subscription cancelled via webhook', ['tenant_id' => $tenant->id]);
        }

        return $response;
    }
}
