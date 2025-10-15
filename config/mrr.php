<?php

declare(strict_types=1);

use App\Models\Checkout;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Price;
use App\Models\Product;
use App\Models\Refund;
use App\Models\Subscription;

return [
    'site_url' => env('SITE_URL'),
    'checkout_domain' => env('CHECKOUT_DOMAIN', 'pagos.wrap.test'),
    'checkout_prefix' => env('CHECKOUT_PREFIX', 'checkout'),
    'customer_portal_domain' => env('CUSTOMER_PORTAL_DOMAIN', 'portal.wrap.test'),
    'customer_portal_prefix' => env('CUSTOMER_PORTAL_PREFIX', 'customers'),
    'currency' => env('APP_CURRENCY', 'USD'),
    'ksuid_prefixes' => [
        class_basename(Customer::class) => 'cus',
        class_basename(Payment::class) => 'pay',
        class_basename(Price::class) => 'price',
        class_basename(Product::class) => 'prod',
        class_basename(Refund::class) => 'ref',
        class_basename(Subscription::class) => 'sub',
        class_basename(Checkout::class) => 'ch',
        class_basename(Order::class) => 'ord',
    ],
    'card_block_customization' => [
        'texts' => [],
        'style' => [
            'theme' => 'default',
            'customVariables' => [
                'formPadding' => '0px',
            ],
        ],
    ],
];
