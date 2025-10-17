<?php

namespace App\Observers;

use App\Events\Subscriptions\SubscriptionCreated;
use App\Events\Subscriptions\SubscriptionUpdated;
use App\Models\Subscription;

class SubscriptionObserver
{
    public function created(Subscription $subscription): void
    {
        event(new SubscriptionCreated($subscription));
    }

    public function updated(Subscription $subscription): void
    {
        event(new SubscriptionUpdated($subscription));
    }
}
