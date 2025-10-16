<?php

declare(strict_types=1);

use App\Http\Controllers\HandshakesController;
use App\Http\Controllers\Webhooks\MercadoPago\SubscriptionsController;
use App\Http\Middleware\Customers\PortalIdentifierContext;
use App\Livewire\Checkout\Callback;
use App\Livewire\Checkout\Completed;
use App\Livewire\Checkout\Pay;
use App\Livewire\CustomerPortal\Home;
use Illuminate\Support\Facades\Route;

Route::get('/handshakes/{handshake:idempotency}', HandshakesController::class)
    ->name('handshake');

Route::prefix('webhooks')->group(function () {
    Route::prefix('mercadopago')->group(function () {
        Route::get('preapprovals/{signature}', [SubscriptionsController::class, 'preapprovalCallback'])
            ->name('mercadopago.preapproval.callback');
    });
});

Route::domain(config('mrr.checkout_domain'))
    ->prefix(config('mrr.checkout_prefix') . '/{checkout:ksuid}')
    ->group(function (): void {
        Route::get('/', Pay::class)->name('checkout');
        Route::get('/complete', Completed::class)->name('checkout.complete');
        Route::get('/callback', Callback::class)->name('checkout.callback');
    });

Route::domain(config('mrr.customer_portal_domain'))
    ->prefix(config('mrr.customer_portal_prefix') . '/{portalIdentifier}')
    ->middleware(PortalIdentifierContext::class)
    ->group(function (): void {
        Route::get('/', Home::class)
            ->name('customer_portal.home');
    });

Route::fallback(function () {
    return '';
});
