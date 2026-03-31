<?php

use App\Http\Controllers\BillingController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\IntegrationsController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SupportController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WebhookController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn() => redirect()->route('login'));

Route::middleware(['auth', 'verified', 'tenant'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Users
    Route::resource('users', UserController::class)->except(['show', 'create', 'edit']);
    Route::put('/users/{user}/role',        [UserController::class, 'updateRole'])->name('users.role');
    Route::put('/users/{user}/reactivate',  [UserController::class, 'reactivate'])->name('users.reactivate');
    Route::delete('/users/{user}/delete',   [UserController::class, 'forceDestroy'])->name('users.force-delete');

    // Billing
    Route::prefix('billing')->name('billing.')->group(function () {
        Route::get('/plans',          [BillingController::class, 'plans'])->name('plans');
        Route::post('/checkout',      [BillingController::class, 'checkout'])->name('checkout');
        Route::get('/success',        [BillingController::class, 'success'])->name('success');
        Route::get('/portal',         [BillingController::class, 'portal'])->name('portal');
        Route::post('/portal/redirect',[BillingController::class, 'redirectToPortal'])->name('portal.redirect');
        Route::post('/cancel',        [BillingController::class, 'cancel'])->name('cancel');
        Route::post('/resume',        [BillingController::class, 'resume'])->name('resume');
    });

    // Support
    Route::get('/support', [SupportController::class, 'index'])->name('support');

    // Integrations (API keys — Enterprise plan)
    Route::prefix('integrations')->name('integrations.')->group(function () {
        Route::get('/',                                        [IntegrationsController::class, 'index'])->name('index');
        Route::post('/api-keys',                              [IntegrationsController::class, 'store'])->name('api-keys.store');
        Route::delete('/api-keys/{apiKey}',                   [IntegrationsController::class, 'destroy'])->name('api-keys.destroy');
    });

    // Profile
    Route::get('/profile',    [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile',  [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Stripe webhooks (no CSRF — Cashier verifies the signature)
Route::post('/stripe/webhook', [WebhookController::class, 'handleWebhook'])
    ->name('cashier.webhook');

require __DIR__.'/auth.php';
