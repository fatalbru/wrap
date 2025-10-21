<?php

declare(strict_types=1);

namespace App\Actions\Subscriptions;

use App\Concerns\Action;
use App\Enums\SubscriptionStatus;
use App\Models\Subscription;
use App\Services\MercadoPago\Subscription as SubscriptionService;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Throwable;

final class CancelSubscription extends Action
{
    public function __construct(private readonly SubscriptionService $subscriptionService) {}

    /**
     * @throws Throwable
     * @throws LockTimeoutException
     */
    public function handle(Subscription $subscription): void
    {
        throw_if(! $subscription->cancelable, 'Cannot cancel subscription');

        $this->lock(function () use ($subscription): void {
            $this->subscriptionService->cancel($subscription->application, $subscription->vendor_id);
            $subscription->canceled_at = now();
            $subscription->status = SubscriptionStatus::CANCELLED;
            $subscription->save();
        }, ...func_get_args());
    }
}
