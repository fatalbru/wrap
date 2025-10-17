<?php

namespace App\Observers;

use App\Events\Orders\OrderCreated;
use App\Events\Orders\OrderUpdated;
use App\Models\Order;

class OrderObserver
{
    public function created(Order $order): void
    {
        event(new OrderCreated($order));
    }

    public function updated(Order $order): void
    {
        event(new OrderUpdated($order));
    }
}
