<?php

declare(strict_types=1);

namespace App\Actions;

use App\Dtos\MercadoPago\Cards\TemporaryCardDto;
use App\Enums\Currency;
use App\Enums\PaymentStatus;
use App\Enums\PaymentVendor;
use App\Enums\ProductType;
use App\Enums\SubscriptionStatus;
use App\Events\Subscriptions\SubscriptionCreated;
use App\Events\Subscriptions\SubscriptionStarted;
use App\Events\Subscriptions\TrialStarted;
use App\Models\Application;
use App\Models\Checkout;
use App\Models\Payment;
use App\Models\Subscription;
use App\Services\MercadoPago\Subscription as SubscriptionService;
use Illuminate\Support\Facades\Log;
use SensitiveParameter;

final readonly class Subscribe
{
    public function __construct(private SubscriptionService $subscriptionService) {}

    /**
     * @throws \Throwable
     */
    public function handle(
        Checkout $checkout,
        #[SensitiveParameter] ?TemporaryCardDto $card = null
    ): Payment {
        /** @var Subscription $subscription */
        $subscription = $checkout->checkoutable;

        $price = $subscription->price;

        $amount = $price->price;

        if ($price->trial_days > 0) {
            $amount = 0;
        }

        $application = Application::assign(
            PaymentVendor::MERCADOPAGO_CARD,
            $subscription->environment,
            ProductType::SUBSCRIPTION
        );

        $subscription->application()->associate($application);
        $subscription->save();

        $response = $this->subscriptionService->subscribe(
            $application,
            $price,
            $checkout->customer->email,
            Currency::from(config('mrr.currency')),
            $subscription->ksuid,
            $card,
            backUrl: url(route('checkout.callback', $checkout)),
        );

        event(new SubscriptionCreated($subscription));

        Log::debug(__CLASS__, $response);

        if (data_get($response, 'status') !== 'authorized') {
            return $subscription->payments()->create([
                'status' => PaymentStatus::REJECTED,
                'customer_id' => $checkout->customer_id,
                'amount' => $amount,
                'decline_reason' => data_get($response, 'code'),
                'vendor_data' => $response,
                'payment_method' => $card?->paymentMethodId(),
                'payment_vendor' => PaymentVendor::MERCADOPAGO_CARD,
                'payment_type' => $card?->paymentTypeId(),
                'card_last_digits' => $card?->lastFourDigits(),
            ]);
        }

        event(new SubscriptionStarted($subscription));

        $subscription->update([
            'status' => SubscriptionStatus::AUTHORIZED,
            'next_payment_at' => data_get($response, 'next_payment_date'),
            'started_at' => now(),
            'vendor_data' => $response,
            'vendor_id' => data_get($response, 'id'),
            ...$price->has_trial ? [
                'trial_started_at' => now(),
                'trial_ended_at' => now()->addDays($price->trial_days),
            ] : [],
        ]);

        if ($price->has_trial) {
            event(new TrialStarted($subscription));
        }

        $checkout->touch('completed_at');

        return $subscription->payments()->create([
            'status' => PaymentStatus::APPROVED,
            'customer_id' => $checkout->customer_id,
            'amount' => $amount,
            'paid_at' => now(),
            'vendor_data' => $response,
            'payment_vendor' => PaymentVendor::MERCADOPAGO_CARD,
            'payment_method' => $card?->paymentMethodId(),
            'payment_type' => $card?->paymentTypeId(),
            'card_last_digits' => $card?->lastFourDigits(),
        ]);
    }
}
