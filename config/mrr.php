<?php

declare(strict_types=1);

use App\Events\Customers\CustomerCreated;
use App\Events\Customers\CustomerDeleted;
use App\Events\Customers\CustomerUpdated;
use App\Events\Orders\OrderCanceled;
use App\Events\Orders\OrderCompleted;
use App\Events\Orders\OrderCreated;
use App\Events\Orders\OrderExpired;
use App\Events\Payments\PaymentAuthorized;
use App\Events\Payments\PaymentCreated;
use App\Events\Payments\PaymentFailed;
use App\Events\Payments\PaymentUpdated;
use App\Events\Refunds\RefundCreated;
use App\Events\Subscriptions\SubscriptionCanceled;
use App\Events\Subscriptions\SubscriptionCreated;
use App\Events\Subscriptions\SubscriptionStarted;
use App\Events\Subscriptions\SubscriptionUpdated;
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
    'webhook_fake' => env('WEBHOOK_FAKE', false),
    'webhook_tolerance' => env('WEBHOOK_TOLERANCE', 300), // in seconds

    'currency' => env('APP_CURRENCY', 'USD'),

    /**
     * KSUID prefixes are used across the platform for the configured models
     */
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

    /**
     * Webhook Event Names
     */
    'webhook_event_name' => [
        CustomerCreated::class => 'customer.created',
        CustomerUpdated::class => 'customer.updated',
        CustomerDeleted::class => 'customer.deleted',
        OrderCreated::class => 'order.created',
        OrderCanceled::class => 'order.canceled',
        OrderCompleted::class => 'order.completed',
        OrderExpired::class => 'order.expired',
        PaymentCreated::class => 'payment.created',
        PaymentUpdated::class => 'payment.updated',
        PaymentFailed::class => 'payment.failed',
        PaymentAuthorized::class => 'payment.authorized',
        RefundCreated::class => 'refund.created',
        SubscriptionCreated::class => 'subscription.created',
        SubscriptionStarted::class => 'subscription.started',
        SubscriptionUpdated::class => 'subscription.updated',
        SubscriptionCanceled::class => 'subscription.canceled',
        TrialEnded::class => 'trial.ended',
        TrialStarted::class => 'trial.started',
    ],
];
