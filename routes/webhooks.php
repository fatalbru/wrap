<?php

declare(strict_types=1);

use App\Http\Controllers\Webhooks\MercadoPagoController;
use App\Http\Middleware\MercadoPago\ValidateWebhookSignature;
use Illuminate\Support\Facades\Route;

Route::post('mercadopago', MercadoPagoController::class)
    ->middleware(ValidateWebhookSignature::class)
    ->name('mercadopago');
