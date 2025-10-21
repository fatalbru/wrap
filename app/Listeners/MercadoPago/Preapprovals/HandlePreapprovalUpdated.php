<?php

namespace App\Listeners\MercadoPago\Preapprovals;

use App\Actions\Payments\CreatePayment;
use App\Actions\Webhooks\RegisterWebhookLog;
use App\DTOs\PaymentMethodDto;
use App\Enums\FrequencyType;
use App\Enums\PaymentMethod;
use App\Enums\PaymentProvider;
use App\Enums\PaymentStatus;
use App\Enums\PaymentVendor;
use App\Enums\SubscriptionStatus;
use App\Events\MercadoPago\WebhookReceived;
use App\Events\Subscriptions\SubscriptionCanceled;
use App\Events\Subscriptions\SubscriptionStarted;
use App\Events\Subscriptions\TrialStarted;
use App\Models\Subscription;
use App\Services\MercadoPago\Subscription as SubscriptionService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Throwable;

class HandlePreapprovalUpdated implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly SubscriptionService $subscriptionService,
        private readonly CreatePayment $createPayment,
        private readonly RegisterWebhookLog $registerWebhookLog,
    ) {}

    /**
     * @throws Throwable
     */
    public function handle(WebhookReceived $event): void
    {
        if ($event->getAction() !== 'updated') {
            return;
        }

        if ($event->getType() !== 'subscription_preapproval') {
            return;
        }

        $subscription = Subscription::query()->where('vendor_id', data_get($event->getPayload(), 'data_id'))->firstOrFail();

        $response = $this->subscriptionService->get($subscription->application, $subscription->vendor_id);

        Log::debug(__CLASS__, Arr::wrap($response));

        $status = SubscriptionStatus::from(data_get($response, 'status'));

        $subscription->status = $status;
        $subscription->vendor_data = $response;
        $subscription->next_payment_at = data_get($response, 'next_payment_date');
        $subscription->save();

        $this->registerWebhookLog->execute(
            $subscription,
            $response,
            paymentProvider: PaymentProvider::MERCADOPAGO
        );

        if ($status === SubscriptionStatus::AUTHORIZED) {
            if (blank($subscription->started_at)) {
                $subscription->started_at = now();
                $subscription->save();

                event(new SubscriptionStarted($subscription));
            }

            if (blank($subscription->checkout->completed_at)) {
                $subscription->checkout->complete();
            }

            if ($subscription->price->trial_days > 0 && blank($subscription->trial_started_at)) {
                /** @var FrequencyType $frequency */
                $frequency = $subscription->price->frequency;

                $subscription->trial_started_at = now();
                $subscription->trial_ends_at = now()->add($frequency->getFrequencyIterations(), $frequency->getFrequencyCarbonInterval());
                $subscription->save();

                event(new TrialStarted($subscription));

                if ($subscription->payments()->doesntExist()) {
                    $this->createPayment->execute(
                        $subscription,
                        0,
                        PaymentStatus::APPROVED,
                        PaymentVendor::MERCADOPAGO,
                        $response,
                        paymentMethod: new PaymentMethodDto([
                            'paymentMethod' => PaymentMethod::MERCADOPAGO,
                        ]),
                        paidAt: now()->toImmutable()
                    );
                }
            }
        }

        if ($status === SubscriptionStatus::CANCELLED && blank($subscription->canceled_at)) {
            $subscription->canceled_at = now();
            $subscription->save();

            event(new SubscriptionCanceled($subscription));
        }
    }
}
