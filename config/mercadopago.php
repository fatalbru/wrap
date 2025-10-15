<?php

declare(strict_types=1);

use App\Environment;

return [
    Environment::LIVE->value => [
        'orders' => [
            'public_key' => env('ORDERS_MERCADOPAGO_PUBLIC_KEY'),
            'access_token' => env('ORDERS_MERCADOPAGO_ACCESS_TOKEN'),
        ],
        'subscriptions' => [
            'public_key' => env('SUBSCRIPTIONS_MERCADOPAGO_PUBLIC_KEY'),
            'access_token' => env('SUBSCRIPTIONS_MERCADOPAGO_ACCESS_TOKEN'),
        ],
    ],
    Environment::TEST->value => [
        'orders' => [
            'public_key' => env('TEST_ORDERS_MERCADOPAGO_PUBLIC_KEY'),
            'access_token' => env('TEST_ORDERS_MERCADOPAGO_ACCESS_TOKEN'),
        ],
        'subscriptions' => [
            'public_key' => env('TEST_SUBSCRIPTIONS_MERCADOPAGO_PUBLIC_KEY'),
            'access_token' => env('TEST_SUBSCRIPTIONS_MERCADOPAGO_ACCESS_TOKEN'),
        ],
    ],
];
