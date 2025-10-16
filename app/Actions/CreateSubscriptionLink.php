<?php

declare(strict_types=1);

namespace App\Actions;

use App\Currency;
use App\Models\Application;
use App\Models\Checkout;
use App\Models\Subscription;
use App\PaymentVendor;
use App\ProductType;
use App\Services\MercadoPago\Subscription as SubscriptionService;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

final readonly class CreateSubscriptionLink
{
    public function __construct(private SubscriptionService $subscriptionService) {}

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

        $response = $this->subscriptionService->subscribe(
            $application,
            $price,
            $checkout->customer->email,
            Currency::from(config('mrr.currency')),
            $subscription->ksuid,
            backUrl: url(route('checkout.callback', $checkout)),
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
