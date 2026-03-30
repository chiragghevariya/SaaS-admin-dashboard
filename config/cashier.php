<?php

return [
    'key'    => env('STRIPE_KEY'),
    'secret' => env('STRIPE_SECRET'),
    'webhook' => [
        'secret'    => env('STRIPE_WEBHOOK_SECRET'),
        'tolerance' => env('CASHIER_WEBHOOK_TOLERANCE', 300),
    ],
    'model'    => \App\Models\Tenant::class,
    'currency' => env('CASHIER_CURRENCY', 'usd'),
    'currency_locale' => env('CASHIER_CURRENCY_LOCALE', 'en'),
    'payment_notification' => null,
    'payment_urls' => [
        'success' => env('CASHIER_PAYMENT_SUCCESS_URL', '/billing/success'),
        'cancel'  => env('CASHIER_PAYMENT_CANCEL_URL', '/billing/plans'),
    ],
    'secret_key_confirmation' => env('CASHIER_SECRET_KEY_CONFIRMATION', 'yes'),
    'logger' => null,

    // Price IDs for the three plans
    'prices' => [
        'starter'    => env('STRIPE_PRICE_STARTER'),
        'pro'        => env('STRIPE_PRICE_PRO'),
        'enterprise' => env('STRIPE_PRICE_ENTERPRISE'),
    ],
];
