<?php

namespace App\Listeners\MercadoPago\Preapprovals;

use App\Enums\FrequencyType;
use App\Enums\PaymentStatus;
use App\Enums\PaymentVendor;
use App\Enums\SubscriptionStatus;
use App\Events\MercadoPago\WebhookReceived;
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

    public function __construct(private readonly SubscriptionService $subscriptionService) {}

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

        $subscription = Subscription::whereVendorId(data_get($event->getPayload(), 'data_id'))->firstOrFail();

        $response = $this->subscriptionService->get($subscription->application, $subscription->vendor_id);

        Log::debug(__CLASS__, Arr::wrap($response));

        $status = SubscriptionStatus::from(data_get($response, 'status'));

        $subscription->update([
            'status' => $status,
            'vendor_data' => $response,
            'next_payment_at' => data_get($response, 'next_payment_date'),
        ]);

        $subscription->webhookLogs()->create([
            'application_id' => $subscription->application->id,
            'vendor' => PaymentVendor::MERCADOPAGO,
            'payload' => $response,
        ]);

        if ($status === SubscriptionStatus::AUTHORIZED) {
            if (blank($subscription->started_at)) {
                $subscription->touch('started_at');
            }

            if (blank($subscription->checkout->completed_at)) {
                $subscription->checkout->touch('completed_at');
            }

            if ($subscription->price->trial_days > 0 && blank($subscription->trial_started_at)) {
                /** @var FrequencyType $frequency */
                $frequency = $subscription->price->frequency;

                $subscription->update([
                    'trial_started_at' => now(),
                    'trial_ended_at' => now()->add($frequency->getFrequencyIterations(), $frequency->getFrequencyCarbonInterval()),
                ]);

                if ($subscription->payments()->doesntExist()) {
                    $subscription->payments()->create([
                        'status' => PaymentStatus::APPROVED,
                        'customer_id' => $subscription->customer_id,
                        'amount' => 0,
                        'paid_at' => now(),
                        'vendor_data' => $response,
                        'payment_method' => 'account_money',
                        'payment_type' => 'account_money',
                    ]);
                }
            }
        }

        if ($status === SubscriptionStatus::CANCELLED) {
            if (blank($subscription->canceled_at)) {
                $subscription->touch('canceled_at');
            }
        }
    }
}
