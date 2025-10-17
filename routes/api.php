<?php

declare(strict_types=1);

use App\Http\Controllers\Api\CheckoutsController;
use App\Http\Controllers\Api\CustomersController;
use App\Http\Controllers\Api\OrdersController;
use App\Http\Controllers\Api\PaymentsController;
use App\Http\Controllers\Api\PricesController;
use App\Http\Controllers\Api\ProductsController;
use App\Http\Controllers\Api\SubscriptionsController;
use Illuminate\Support\Facades\Route;

Route::namespace('Api')->middleware('auth:sanctum')->group(function (): void {
    Route::prefix('customers')
        ->group(function (): void {
            Route::get('/', [CustomersController::class, 'index'])
                ->name('api.customers.index');

            Route::post('/', [CustomersController::class, 'store'])
                ->name('api.customers.store');

            Route::prefix('{customer:ksuid}')
                ->group(function (): void {
                    Route::get('/', [CustomersController::class, 'show'])
                        ->name('api.customers.show');

                    Route::post('/portal-link', [CustomersController::class, 'portalUrl'])
                        ->name('api.customers.portal-link');

                    Route::put('/', [CustomersController::class, 'update'])
                        ->name('api.customers.update');
                });
        });

    Route::prefix('products')->group(function (): void {
        Route::get('/', [ProductsController::class, 'index'])
            ->name('api.products.index');
    });

    Route::prefix('orders')
        ->group(function (): void {
            Route::get('/', [OrdersController::class, 'index'])
                ->name('api.orders.index');

            Route::prefix('{order:ksuid}')->group(function (): void {
                Route::get('/', [OrdersController::class, 'show'])
                    ->name('api.orders.show');
            });
        });

    Route::prefix('prices')->group(function (): void {
        Route::get('/{product:ksuid?}', [PricesController::class, 'index'])
            ->name('api.prices.index');
    });

    Route::prefix('payments')
        ->group(function (): void {
            Route::get('/', [PaymentsController::class, 'index'])
                ->name('api.payments.index');

            Route::prefix('{payment:ksuid}')
                ->group(function (): void {
                    Route::post('/refund', [PaymentsController::class, 'refund'])
                        ->name('api.payments.refund');
                });
        });

    Route::prefix('subscriptions')
        ->group(function (): void {
            Route::get('/', [SubscriptionsController::class, 'index'])
                ->name('api.subscriptions.index');

            Route::prefix('{subscription:ksuid}')
                ->group(function (): void {
                    Route::get('/', [SubscriptionsController::class, 'show'])
                        ->name('api.subscriptions.show');

                    Route::get('/raw', [SubscriptionsController::class, 'raw'])
                        ->name('api.subscriptions.raw');

                    Route::post('/cancel', [SubscriptionsController::class, 'cancel'])
                        ->name('api.subscriptions.cancel');

                    Route::post('/update-plan', [SubscriptionsController::class, 'updatePlan'])
                        ->name('api.subscriptions.update-plan');

                    Route::get('/payments', [SubscriptionsController::class, 'payments'])
                        ->name('api.subscriptions.payments');
                });
        });

    Route::prefix('checkouts')->group(function (): void {
        Route::post('/', [CheckoutsController::class, 'store'])
            ->name('api.checkouts.store');

        Route::prefix('{checkout:ksuid}')->group(function (): void {
            Route::get('/', [CheckoutsController::class, 'show'])
                ->name('api.checkouts.show');
        });
    });
});
