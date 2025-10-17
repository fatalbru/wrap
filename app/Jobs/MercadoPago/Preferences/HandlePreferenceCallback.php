<?php

namespace App\Jobs\MercadoPago\Preferences;

use App\Jobs\MercadoPago\MerchantOrders\ProcessMerchantOrder;
use App\Models\Order;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class HandlePreferenceCallback implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly array $payload
    )
    {
    }

    public function handle(): void
    {
        Log::debug(__CLASS__, $this->payload);

        $order = Order::findOrFail(data_get($this->payload, 'order_id'));

        if (data_get($this->payload, 'topic') === 'merchant_order') {
            Log::debug('Delegating payload to ProcessMerchantOrder', compact('order'));

            dispatch(new ProcessMerchantOrder(
                data_get($this->payload, 'id'),
                $order
            ));

            return;
        }
    }
}
