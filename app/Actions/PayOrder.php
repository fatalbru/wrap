<?php

declare(strict_types=1);

namespace App\Actions;

use App\Dtos\MercadoPago\Cards\TemporaryCardDto;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\PaymentVendor;
use App\Enums\ProductType;
use App\Events\Orders\OrderCompleted;
use App\Events\Orders\OrderCreated;
use App\Events\Payments\PaymentAuthorized;
use App\Events\Payments\PaymentFailed;
use App\Models\Application;
use App\Models\Checkout;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Services\MercadoPago\Payment as PaymentService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use SensitiveParameter;

final readonly class PayOrder
{
    public function __construct(private PaymentService $paymentService)
    {
    }

    public function handle(
        Checkout                               $checkout,
        #[SensitiveParameter] TemporaryCardDto $card,
        array                                  $metadata = []
    ): Payment
    {
        $idempotency = Str::random(128);

        /** @var Order $order */
        $order = $checkout->checkoutable;

        $application = Application::assign(
            PaymentVendor::MERCADOPAGO_CARD,
            $order->environment,
            ProductType::ORDER
        );

        $order->application()->associate($application);
        $order->save();

        $items = $order->items->map(fn(OrderItem $orderItem) => [
            'id' => $orderItem->id,
            'title' => $orderItem->price->name,
            'quantity' => $orderItem->quantity,
            'unit_price' => $orderItem->price->price,
            'subtotal' => $orderItem->quantity * $orderItem->price->price,
        ]);

        $total = $items->sum('subtotal');

        $response = $this->paymentService->create(
            $application,
            $card,
            $checkout->customer->email,
            $order->ksuid,
            __('Order :ksuid', $order->only('ksuid')),
            $total,
            $idempotency,
        );

        Log::debug(__CLASS__, $response);

        if (data_get($response, 'status') !== 'approved') {
            $payment = $order->payments()->create([
                'customer_id' => $checkout->customer_id,
                'amount' => $total,
                'status' => PaymentStatus::REJECTED,
                'decline_reason' => data_get($response, 'status_detail'),
                'vendor_data' => $response,
                'payment_vendor' => PaymentVendor::MERCADOPAGO_CARD,
                'payment_method' => $card?->paymentMethodId(),
                'payment_type' => $card?->paymentTypeId(),
                'card_last_digits' => $card?->lastFourDigits(),
            ]);

            return $payment;
        }

        $order->complete();

        $checkout->touch('completed_at');

        $payment = $order->payments()->create([
            'customer_id' => $checkout->customer_id,
            'amount' => $total,
            'status' => PaymentStatus::APPROVED,
            'paid_at' => now(),
            'payment_vendor' => PaymentVendor::MERCADOPAGO_CARD,
            'vendor_id' => data_get($response, 'id'),
            'vendor_data' => $response,
            'payment_method' => $card?->paymentMethodId(),
            'payment_type' => $card?->paymentTypeId(),
            'card_last_digits' => $card?->lastFourDigits(),
        ]);

        return $payment;
    }
}
