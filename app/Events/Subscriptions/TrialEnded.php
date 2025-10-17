<?php

namespace App\Events\Subscriptions;

use App\Interfaces\OutgoingWebhookInterface;
use App\Models\Subscription;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TrialEnded implements OutgoingWebhookInterface
{
    use Dispatchable, SerializesModels;

    public function __construct(public readonly Subscription $subscription)
    {
    }

    function getWebhookData(): array
    {
        return [];
    }
}
