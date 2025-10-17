<?php

namespace App\Events\Subscriptions;

use App\Interfaces\OutgoingWebhookInterface;
use App\Models\Subscription;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SubscriptionUpdated implements OutgoingWebhookInterface
{
    use Dispatchable, SerializesModels;

    public function __construct(public readonly Subscription $subscription) {}

    public function getWebhookData(): array
    {
        return [];
    }

    public function getModel(): Model
    {
        return $this->subscription;
    }
}
