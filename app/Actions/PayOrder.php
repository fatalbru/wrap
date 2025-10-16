<?php

declare(strict_types=1);

namespace App\Actions;

use App\Dtos\MercadoPago\Cards\TemporaryCardDto;
use App\Models\Application;
use App\Models\Checkout;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\OrderStatus;
use App\PaymentStatus;
use App\PaymentVendor;
use App\ProductType;
use App\Services\MercadoPago\Payment as PaymentService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use SensitiveParameter;

final readonly class PayOrder
{
    public function __construct(private PaymentService $paymentService) {}

    public function handle(
        Checkout $checkout,
        #[SensitiveParameter] TemporaryCardDto $card,
        array $metadata = []
    ): Payment {
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

        $items = $order->items->map(fn (OrderItem $orderItem) => [
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
            $items->pluck('title')->implode(', ')." {$order->ksuid}",
            $total,
            $idempotency,
            metadata: [
                ...$metadata,
                'items' => $items->toArray(),
            ],
        );

        Log::debug(__CLASS__, $response);

        if (data_get($response, 'status') !== 'approved') {
            return $order->payments()->create([
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
        }

        $order->update([
            'status' => OrderStatus::COMPLETED,
            'completed_at' => now(),
        ]);

        $checkout->touch('completed_at');

        return $order->payments()->create([
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
    }
}
