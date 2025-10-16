<?php

declare(strict_types=1);

namespace App\Actions;

use App\Currency;
use App\HandshakeType;
use App\Models\Application;
use App\Models\Checkout;
use App\Models\Handshake;
use App\Models\Subscription;
use App\PaymentVendor;
use App\ProductType;
use App\Services\MercadoPago\Subscription as SubscriptionService;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

final readonly class CreateSubscriptionLink
{
    public function __construct(private SubscriptionService $subscriptionService)
    {
    }

    public function handle(Checkout $checkout): array
    {
        /** @var Subscription $subscription */
        $subscription = $checkout->checkoutable;

        if (filled(data_get($subscription->vendor_data, 'init_point'))) {
            return $subscription->vendor_data;
        }

        $application = Application::assign(
            PaymentVendor::MERCADOPAGO,
            $subscription->environment,
            ProductType::SUBSCRIPTION
        );

        $subscription->application()->associate($application);
        $subscription->save();

        $price = $subscription->price;

        $idempotency = md5($subscription->ksuid . Str::random(128));

        $handshake = Handshake::create([
            'type' => HandshakeType::REROUTE,
            'idempotency' => $idempotency,
            'payload' => [
                'route' => 'mercadopago.preapproval.callback',
                'routeParams' => [
                    'signature' => encrypt([
                        'idempotency' => $idempotency,
                        'checkout_id' => $checkout->id,
                        'subscription_id' => $subscription->id,
                    ])
                ]
            ]
        ]);

        $response = $this->subscriptionService->subscribe(
            $application,
            $price,
            $checkout->customer->email,
            Currency::from(config('mrr.currency')),
            $subscription->ksuid,
            backUrl: url(route('handshake', $handshake->idempotency)),
            metadata: [$subscription->ksuid],
        );

        Log::debug(__CLASS__, Arr::wrap($response));

        $subscription->update([
            'vendor_id' => data_get($response, 'id'),
            'vendor_data' => $response,
        ]);

        return $response;
    }
}
