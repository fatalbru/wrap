<?php

namespace App\Jobs\MercadoPago\MerchantOrders;

use App\Jobs\MercadoPago\Payments\RegisterModelPayment;
use App\Models\Order;
use App\Services\MercadoPago\MerchantOrder as MerchantOrderService;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class ProcessMerchantOrder implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly int $merchantOrderId,
        private readonly Order $order
    ) {}

    public function failed()
    {
        Log::error(__CLASS__, [
            'merchant_order_id' => $this->merchantOrderId,
            'order' => $this->order,
        ]);
    }

    /**
     * @throws Exception
     */
    public function handle(MerchantOrderService $merchantOrderService): void
    {
        $merchantOrder = $merchantOrderService->get(
            $this->order->application,
            $this->merchantOrderId
        );

        if ($this->order->ksuid !== data_get($merchantOrder, 'external_reference')) {
            throw new Exception('External reference does not match provided Order');
        }

        if (data_get($merchantOrder, 'order_status') === 'paid') {
            foreach (data_get($merchantOrder, 'payments') as $payment) {
                dispatch(new RegisterModelPayment(
                    data_get($payment, 'id'),
                    $this->order
                ));
            }
        }
    }
}
