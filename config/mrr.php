<?php

declare(strict_types=1);

use App\Events\Orders\OrderCanceled;
use App\Events\Orders\OrderCompleted;
use App\Events\Orders\OrderCreated;
use App\Events\Orders\OrderExpired;
use App\Events\Payments\PaymentCreated;
use App\Events\Payments\PaymentUpdated;
use App\Events\Refunds\RefundCreated;
use App\Events\Subscriptions\SubscriptionCanceled;
use App\Events\Subscriptions\SubscriptionCreated;
use App\Events\Subscriptions\SubscriptionStarted;
use App\Events\Subscriptions\TrialEnded;
use App\Events\Subscriptions\TrialStarted;
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
    'webhook_url' => env('WEBHOOK_URL'),
    'webhook_signature' => env('WEBHOOK_SIGNATURE'),
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

    /**
     * Webhook Event Names
     */
    'webhook_event_name' => [
        OrderCanceled::class => 'order.canceled',
        OrderCompleted::class => 'order.completed',
        OrderCreated::class => 'order.created',
        OrderExpired::class => 'order.expired',
        PaymentCreated::class => 'payment.created',
        PaymentUpdated::class => 'payment.updated',
        RefundCreated::class => 'refund.created',
        SubscriptionCanceled::class => 'subscription.canceled',
        SubscriptionCreated::class => 'subscription.created',
        SubscriptionStarted::class => 'subscription.started',
        TrialEnded::class => 'trial.ended',
        TrialStarted::class => 'trial.started',
    ]
];
