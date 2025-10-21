<?php

declare(strict_types=1);

namespace App\Actions\Subscriptions;

use App\Concerns\Action;
use App\Enums\ProductType;
use App\Enums\SubscriptionStatus;
use App\Models\Price;
use App\Models\Subscription;
use App\Services\MercadoPago\Subscription as SubscriptionService;
use Throwable;

final class UpdateSubscriptionPrice extends Action
{
    public function __construct(private readonly SubscriptionService $subscriptionService) {}

    /**
     * @throws Throwable
     */
    public function handle(Subscription $subscription, Price $price): void
    {
        throw_if($subscription->status === SubscriptionStatus::CANCELLED, 'Cannot modify canceled subscription.');
        throw_if($price->product->type !== ProductType::SUBSCRIPTION, 'Only subscription prices eligible.');
        throw_if($subscription->environment !== $price->environment, 'Environments do not match.');

        if (!$subscription->price()->is($price)) {
            $this->lock(function () use ($subscription, $price): void {
                $this->subscriptionService->updatePreapproval(
                    $subscription->application,
                    $subscription->vendor_id,
                    $price->price,
                );

                $subscription->price()->associate($price);
                $subscription->save();
            }, ...func_get_args());
        }
    }
}
