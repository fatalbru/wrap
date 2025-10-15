<?php

namespace App\Jobs\Subscriptions;

use App\FrequencyType;
use App\Models\Subscription;
use App\PaymentStatus;
use App\Services\MercadoPago\Subscription as SubscriptionService;
use App\SubscriptionStatus;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ReviewSubscriptionStatus implements ShouldQueue
{
    use Queueable;

    public function __construct(protected readonly Subscription $subscription) {}

    /**
     * Execute the job.
     */
    public function handle(SubscriptionService $subscriptionService): void
    {
        $nextPaymentAt = $this->subscription->next_payment_at;

        $payments = $this->subscription->payments()
            ->where('created_at', '>=', $nextPaymentAt)
            ->get();

        if ($nextPaymentAt->isPast()) {
            if ($payments->where('status', PaymentStatus::APPROVED)->isNotEmpty()) {
                /** @var FrequencyType $frequency */
                $frequency = $this->subscription->price->frequency;

                $this->subscription->update([
                    'status' => SubscriptionStatus::ACTIVE,
                    'current_period_start' => now(),
                    'current_period_end' => now()->add(
                        $frequency->getFrequencyIterations(),
                        $frequency->getFrequencyCarbonInterval()
                    ),
                ]);
            } else {
                $subscriptionData = $subscriptionService->get(
                    $this->subscription->vendor_id,
                    $this->subscription->environment
                );

                $attemptedStatus = SubscriptionStatus::tryFrom(data_get($subscriptionData, 'status'));

                if ($attemptedStatus === SubscriptionStatus::CANCELLED) {
                    if (blank($this->subscription->canceled_at)) {
                        $this->subscription->touch('canceled_at');
                    }

                    if (blank($this->subscription->ended_at)) {
                        $this->subscription->update([
                            'ended_at' => $this->subscription->current_period_end,
                        ]);
                    }
                }
            }
        }
    }
}
