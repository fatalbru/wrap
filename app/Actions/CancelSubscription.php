<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Subscription;
use App\Services\MercadoPago\Subscription as SubscriptionService;
use App\SubscriptionStatus;

final readonly class CancelSubscription
{
    public function __construct(private SubscriptionService $subscriptionService) {}

    public function handle(Subscription $subscription): void
    {
        if ($subscription->cancelable) {
            $this->subscriptionService->cancel($subscription->application, $subscription->vendor_id);
            $subscription->touch('canceled_at');
            $subscription->update([
                'status' => SubscriptionStatus::CANCELLED,
            ]);
        }
    }
}
