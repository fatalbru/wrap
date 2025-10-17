<?php

namespace App\Events\Orders;

use App\Interfaces\OutgoingWebhookInterface;
use App\Models\Order;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderCreated implements OutgoingWebhookInterface
{
    use Dispatchable, SerializesModels;

    public function __construct(readonly Order $order)
    {
    }

    function getWebhookData(): array
    {
        return [];
    }
}
