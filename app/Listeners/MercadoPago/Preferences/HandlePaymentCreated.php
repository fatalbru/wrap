<?php

namespace App\Listeners\MercadoPago\Preferences;

use App\Actions\Payments\CreatePayment;
use App\Actions\Webhooks\RegisterWebhookLog;
use App\DTOs\PaymentMethodDto;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentProvider;
use App\Enums\PaymentStatus;
use App\Enums\PaymentVendor;
use App\Events\MercadoPago\WebhookReceived;
use App\Models\Order;
use App\Services\MercadoPago\Payment as PaymentService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class HandlePaymentCreated implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly PaymentService     $paymentService,
        private readonly RegisterWebhookLog $registerWebhookLog,
        private readonly CreatePayment      $createPayment,
    )
    {
    }

    /**
     * @throws Throwable
     */
    public function handle(WebhookReceived $event): void
    {
        if ($event->getAction() !== 'payment.created') {
            Log::debug(__CLASS__, [
                'message' => 'Only payment.created events handled',
            ]);

            return;
        }

        if (!$event->getRelatedModel() instanceof Order) {
            Log::debug(__CLASS__, [
                'message' => 'Only Order models are handled',
            ]);

            return;
        }

        $order = $event->getRelatedModel();

        $payment = $this->paymentService->get($order->application, data_get($event->getPayload(), 'data_id'));

        if (Str::startsWith(data_get($payment, 'description'), '(Sub.)')) {
            Log::debug(__CLASS__, [
                'message' => 'Only Order models are handled, Subscription found',
            ]);

            return;
        }

        $status = PaymentStatus::from(data_get($payment, 'status'));

        $this->createPayment->execute(
            $order,
            data_get($payment, 'transaction_amount'),
            $status,
            PaymentVendor::MERCADOPAGO,
            $payment,
            when($status !== PaymentStatus::APPROVED, data_get($payment, 'status_detail')),
            paymentMethod: new PaymentMethodDto([
                'paymentMethod' => PaymentMethod::CARD,
                ... $payment
            ]),
            paidAt: when($status === PaymentStatus::APPROVED, now()->toImmutable()),
            vendorId: data_get($payment, 'id'),
        );

        if ($status === PaymentStatus::APPROVED && $order->status !== OrderStatus::COMPLETED) {
            $order->status = OrderStatus::COMPLETED;
            $order->save();

            $order->checkout->complete();
        }

        $this->registerWebhookLog->execute($order, $payment, paymentProvider: PaymentProvider::MERCADOPAGO);

        Log::debug(__CLASS__, [$payment]);
    }
}
