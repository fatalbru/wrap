<?php

declare(strict_types=1);

namespace App\Actions\Subscriptions;

use App\Actions\Applications\AssignApplication;
use App\Actions\Payments\CreatePayment;
use App\Concerns\Action;
use App\DTOs\PaymentMethodDto;
use App\Enums\Currency;
use App\Enums\PaymentStatus;
use App\Enums\PaymentVendor;
use App\Enums\ProductType;
use App\Enums\SubscriptionStatus;
use App\Events\Subscriptions\SubscriptionCreated;
use App\Events\Subscriptions\SubscriptionStarted;
use App\Events\Subscriptions\TrialStarted;
use App\Models\Checkout;
use App\Models\Payment;
use App\Models\Subscription;
use App\Services\MercadoPago\Subscription as SubscriptionService;
use App\Services\MercadoPago\Preapproval as PreapprovalService;
use Illuminate\Support\Facades\Log;
use SensitiveParameter;
use Throwable;

final class Subscribe extends Action
{
    public function __construct(
        private readonly SubscriptionService $subscriptionService,
        private readonly PreapprovalService  $preapprovalService,
        private readonly AssignApplication   $assignApplication,
        private readonly CreatePayment       $createPayment
    )
    {
    }

    /**
     * @throws Throwable
     */
    public function handle(
        Checkout                                $checkout,
        #[SensitiveParameter] ?PaymentMethodDto $paymentMethod = null
    ): Payment
    {
        return $this->lock(function () use ($checkout, $paymentMethod) {
            /** @var Subscription $subscription */
            $subscription = $checkout->checkoutable;

            $price = $subscription->price;

            $amount = $price->price;

            if ($price->trial_days > 0) {
                $amount = 0;
            }

            $subscription->application()->associate(
                $this->assignApplication->handle(
                    $subscription->environment,
                    PaymentVendor::MERCADOPAGO_CARD,
                    ProductType::SUBSCRIPTION
                )
            );
            $subscription->save();

            $response = $this->subscriptionService->subscribe(
                $subscription->application,
                $price,
                $checkout->customer->email,
                Currency::from(config('wrap.currency')),
                $subscription->ksuid,
                $paymentMethod,
                backUrl: url(route('checkout.callback', $checkout)),
            );

            event(new SubscriptionCreated($subscription));

            Log::debug(__CLASS__, $response);

            $status = PaymentStatus::APPROVED;
            $declineReason = null;

            if (data_get($response, 'status') !== 'authorized') {
                $status = PaymentStatus::REJECTED;
                $declineReason = data_get($response, 'message', data_get($response, 'code'));
            }

            $payment = $this->createPayment->handle(
                $subscription,
                $amount,
                $status,
                PaymentVendor::MERCADOPAGO_CARD,
                $response,
                $declineReason,
                $paymentMethod
            );

            if ($payment->isSuccessful()) {
                event(new SubscriptionStarted($subscription));

                $subscription->status = SubscriptionStatus::AUTHORIZED;
                $subscription->next_payment_at = data_get($response, 'next_payment_date');
                $subscription->started_at = now();
                $subscription->vendor_data = $response;
                $subscription->vendor_id = data_get($response, 'id');

                if ($price->has_trial) {
                    $subscription->trial_started_at = now();
                    $subscription->trial_ended_at = now()->addDays($price->trial_days);
                }

                $subscription->save();

                if ($price->has_trial) {
                    event(new TrialStarted($subscription));
                }

                $checkout->complete();
            }

            return $payment;
        }, ...func_get_args());
    }
}
