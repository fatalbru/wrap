<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Price;
use App\Models\Subscription;
use App\Enums\ProductType;
use App\Services\MercadoPago\Subscription as SubscriptionService;
use App\Enums\SubscriptionStatus;
use Throwable;

final readonly class UpdateSubscriptionPrice
{
    public function __construct(private SubscriptionService $subscriptionService) {}

    /**
     * @throws Throwable
     */
    public function handle(Subscription $subscription, Price $price): void
    {
        throw_if($subscription->status === SubscriptionStatus::CANCELLED, 'Cannot modify canceled subscription.');

        if ($subscription->price_id !== $price->id) {
            throw_if($price->product->type !== ProductType::SUBSCRIPTION, 'Only subscription prices eligible.');
            throw_if($subscription->environment !== $price->environment, 'Environments do not match.');

            $this->subscriptionService->updatePreapproval(
                $subscription->application,
                $subscription->vendor_id,
                $price->price,
            );

            $subscription->price()->associate($price);
            $subscription->save();
        }
    }
}
