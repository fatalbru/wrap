<?php

namespace App\Events\Orders;

use App\Interfaces\OutgoingWebhookInterface;
use App\Models\Order;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderCanceled implements OutgoingWebhookInterface
{
    use Dispatchable, SerializesModels;

    public function __construct(public readonly Order $order) {}

    public function getWebhookData(): array
    {
        return [];
    }

    public function getModel(): Model
    {
        return $this->order;
    }
}
