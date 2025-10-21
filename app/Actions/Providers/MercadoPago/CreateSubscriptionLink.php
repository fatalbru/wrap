<?php

declare(strict_types=1);

namespace App\Actions\Providers\MercadoPago;

use App\Actions\Applications\AssignApplication;
use App\Concerns\Action;
use App\DTOs\MercadoPago\Preapprovals\PreapprovalLinkDto;
use App\Enums\Currency;
use App\Enums\HandshakeType;
use App\Enums\PaymentVendor;
use App\Enums\ProductType;
use App\Events\Subscriptions\SubscriptionCreated;
use App\Models\Checkout;
use App\Models\Handshake;
use App\Models\Subscription;
use App\Services\MercadoPago\Subscription as SubscriptionService;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Throwable;

final class CreateSubscriptionLink extends Action
{
    public function __construct(
        private readonly SubscriptionService $subscriptionService,
        private readonly AssignApplication   $assignApplication,
    )
    {
    }

    /**
     * @throws LockTimeoutException
     * @throws Throwable
     */
    public function execute(Checkout $checkout): PreapprovalLinkDto
    {
        return $this->lock(function () use ($checkout) {
            /** @var Subscription $subscription */
            $subscription = $checkout->checkoutable;

            if (blank(data_get($subscription->vendor_data, 'init_point'))) {
                $subscription->application()->associate(
                    $this->assignApplication->execute(
                        $subscription->environment,
                        PaymentVendor::MERCADOPAGO,
                        ProductType::SUBSCRIPTION
                    )
                );

                $subscription->save();

                $price = $subscription->price;

                $idempotency = md5($subscription->ksuid . uniqid() . time());

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
                            ]),
                        ],
                    ],
                ]);

                $response = $this->subscriptionService->subscribe(
                    $subscription->application,
                    $price,
                    $checkout->customer->email,
                    Currency::from(config('wrap.currency')),
                    $subscription->ksuid,
                    backUrl: url(route('handshake', $handshake->idempotency)),
                    metadata: [$subscription->ksuid],
                );

                event(new SubscriptionCreated($subscription));

                Log::debug(__CLASS__, Arr::wrap($response));

                $subscription->update([
                    'vendor_id' => data_get($response, 'id'),
                    'vendor_data' => $response,
                ]);
            }

            return new PreapprovalLinkDto($subscription->vendor_data);
        }, ...func_get_args());
    }
}
