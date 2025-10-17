<?php

namespace App\Listeners\MercadoPago\Preferences;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\PaymentVendor;
use App\Events\MercadoPago\WebhookReceived;
use App\Models\Order;
use App\Services\MercadoPago\Payment as PaymentService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class HandlePaymentCreated implements ShouldQueue
{
    use Queueable;

    public function __construct(private readonly PaymentService $paymentService) {}

    /**
     * @throws \Throwable
     */
    public function handle(WebhookReceived $event): void
    {
        if ($event->getAction() !== 'payment.created') {
            Log::debug(__CLASS__, [
                'message' => 'Only payment.created events handled',
            ]);

            return;
        }

        if (! $event->getRelatedModel() instanceof Order) {
            Log::debug(__CLASS__, [
                'message' => 'Only Order models are handled',
            ]);

            return;
        }

        $order = $event->getRelatedModel();

        $payment = $this->paymentService->get(
            $order->application,
            data_get($event->getPayload(), 'data_id')
        );

        if (Str::startsWith(data_get($payment, 'description'), '(Sub.)')) {
            Log::debug(__CLASS__, [
                'message' => 'Only Order models are handled, Subscription found',
            ]);

            return;
        }

        $status = PaymentStatus::from(data_get($payment, 'status'));

        $order->payments()->create([
            'customer_id' => $order->customer_id,
            'amount' => data_get($payment, 'transaction_amount'),
            'status' => $status,
            'decline_reason' => $status !== PaymentStatus::APPROVED ? data_get($payment, 'status_detail') : null,
            'vendor_data' => $payment,
            'vendor_id' => data_get($payment, 'id'),
            'payment_vendor' => PaymentVendor::MERCADOPAGO,
            'payment_method' => data_get($payment, 'payment_method_id'),
            'payment_type' => data_get($payment, 'payment_type_id'),
            'card_last_digits' => data_get($payment, 'card.last_four_digits'),
        ]);

        if ($status === PaymentStatus::APPROVED && $order->status !== OrderStatus::COMPLETED) {
            $order->update([
                'status' => OrderStatus::COMPLETED,
            ]);

            $order->checkout->touch('completed_at');
        }

        $order->webhookLogs()->create([
            'application_id' => $order->application->id,
            'vendor' => PaymentVendor::MERCADOPAGO,
            'payload' => $payment,
        ]);

        Log::debug(__CLASS__, [$payment]);
    }
}
