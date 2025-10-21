<?php

declare(strict_types=1);

use App\Http\Controllers\HandshakesController;
use App\Http\Controllers\Webhooks\MercadoPago\PreapprovalsController;
use App\Http\Controllers\Webhooks\MercadoPago\PreferencesController;
use App\Http\Middleware\Checkouts\ValidateCheckoutExpiration;
use App\Http\Middleware\Customers\PortalIdentifierContext;
use App\Livewire\Checkout\Callback;
use App\Livewire\Checkout\Completed;
use App\Livewire\Checkout\Expired;
use App\Livewire\Checkout\Pay;
use App\Livewire\CustomerPortal\Home;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Support\Facades\Route;

Route::match(['get', 'post'], '/handshakes/{handshake:idempotency}', HandshakesController::class)
    ->withoutMiddleware(VerifyCsrfToken::class)
    ->name('handshake');

Route::prefix('webhooks')->group(function (): void {
    Route::prefix('mercadopago')->group(function (): void {
        Route::get('preapprovals/{signature}', [PreapprovalsController::class, 'preapprovalCallback'])
            ->name('mercadopago.preapproval.callback');

        Route::get('preferences/{signature}', [PreferencesController::class, 'ipn'])
            ->name('mercadopago.preference.callback');
    });
});

Route::domain(config('wrap.checkout_domain'))
    ->prefix(config('wrap.checkout_prefix') . '/{checkout:ksuid}')
    ->middleware(ValidateCheckoutExpiration::class)
    ->group(function (): void {
        Route::get('/', Pay::class)->name('checkout');
        Route::get('/complete', Completed::class)->name('checkout.complete');
        Route::get('/callback', Callback::class)->name('checkout.callback');
        Route::get('/expired', Expired::class)
            ->withoutMiddleware(ValidateCheckoutExpiration::class)
            ->name('checkout.expired');
    });

Route::domain(config('wrap.customer_portal_domain'))
    ->prefix(config('wrap.customer_portal_prefix') . '/{portalIdentifier}')
    ->middleware(PortalIdentifierContext::class)
    ->group(function (): void {
        Route::get('/', Home::class)
            ->name('customer_portal.home');
    });

Route::fallback(function () {
    return '';
});
