<?php

namespace App\Events\Orders;

use App\Interfaces\OutgoingWebhookInterface;
use App\Models\Order;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderCompleted implements OutgoingWebhookInterface
{
    use Dispatchable, SerializesModels;

    public function __construct(readonly Order $order)
    {
    }

    function getWebhookData(): array
    {
        return [];
    }

    function getModel(): Model
    {
        return $this->order;
    }
}
