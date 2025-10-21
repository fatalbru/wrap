<?php

declare(strict_types=1);

namespace App\Actions\Orders;

use App\Actions\Applications\AssignApplication;
use App\Actions\Payments\CreatePayment;
use App\Concerns\Action;
use App\DTOs\PaymentMethodDto;
use App\Enums\PaymentStatus;
use App\Enums\PaymentVendor;
use App\Enums\ProductType;
use App\Events\Orders\OrderCompleted;
use App\Models\Checkout;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Services\MercadoPago\Payment as PaymentService;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use SensitiveParameter;
use Throwable;

final class PayOrder extends Action
{
    public function __construct(
        private readonly PaymentService $paymentService,
        private readonly CreatePayment $createPayment,
        private readonly AssignApplication $assignApplication,
    ) {}

    /**
     * @throws LockTimeoutException
     * @throws Throwable
     */
    public function execute(
        Checkout $checkout,
        #[SensitiveParameter] PaymentMethodDto $paymentMethod,
    ): Payment {
        return $this->lock(function () use ($checkout, $paymentMethod) {
            $idempotency = Str::random(128);

            /** @var Order $order */
            $order = $checkout->checkoutable;

            $order->application()->associate(
                $this->assignApplication->execute(
                    $order->environment,
                    PaymentVendor::MERCADOPAGO_CARD,
                    ProductType::ORDER
                ),
            );
            $order->save();

            $items = $order->items->map(fn (OrderItem $orderItem) => [
                'id' => $orderItem->id,
                'title' => $orderItem->price->name,
                'quantity' => $orderItem->quantity,
                'unit_price' => $orderItem->price->price,
                'subtotal' => $orderItem->quantity * $orderItem->price->price,
            ]);

            $total = $items->sum('subtotal');

            $response = $this->paymentService->create(
                $order->application,
                $paymentMethod,
                $checkout->customer->email,
                $order->ksuid,
                __('Order :ksuid', $order->only('ksuid')),
                $total,
                $idempotency,
            );

            Log::debug(__CLASS__, $response);

            $status = PaymentStatus::APPROVED;
            $declineReason = null;

            if (data_get($response, 'status') !== 'approved') {
                $status = PaymentStatus::REJECTED;
                $declineReason = data_get($response, 'status_detail');
            }

            $payment = $this->createPayment->execute(
                $order,
                $total,
                $status,
                PaymentVendor::MERCADOPAGO_CARD,
                $response,
                $declineReason,
                $paymentMethod
            );

            if ($payment->isSuccessful()) {
                $order->complete();
                $checkout->complete();

                event(new OrderCompleted($order));
            }

            return $payment;
        }, ...func_get_args());
    }
}
