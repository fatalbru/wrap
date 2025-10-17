<?php

namespace App\Listeners\Orders;

use App\Enums\OrderStatus;
use App\Events\Payments\Created;
use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class PaymentCreatedPipeline implements ShouldQueue
{
    use Queueable;

    public function handle(Created $event): void
    {
        if (!$event->payment->payable instanceof Order) {
            return;
        }

        if ($event->payment->isSuccessful()) {
            $order = $event->payment->payable;

            if ($order->status !== OrderStatus::COMPLETED) {
                $order->update([
                    'status' => OrderStatus::COMPLETED,
                    'completed_at' => now(),
                ]);

                $order->checkout?->touch('completed_at');
            }
        }
    }
}
