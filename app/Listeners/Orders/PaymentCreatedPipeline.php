<?php

namespace App\Listeners\Orders;

use App\Enums\OrderStatus;
use App\Events\Orders\OrderCompleted;
use App\Events\Payments\PaymentCreated;
use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class PaymentCreatedPipeline implements ShouldQueue
{
    use Queueable;

    public function handle(PaymentCreated $event): void
    {
        if (! $event->payment->payable instanceof Order) {
            return;
        }

        if ($event->payment->isSuccessful()) {
            /** @var Order $order */
            $order = $event->payment->payable;

            if ($order->status !== OrderStatus::COMPLETED) {
                $order->complete();
                $order->checkout?->touch('completed_at');
                event(new OrderCompleted($order));
            }
        }
    }
}
