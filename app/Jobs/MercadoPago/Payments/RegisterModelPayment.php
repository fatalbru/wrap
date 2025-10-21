<?php

namespace App\Jobs\MercadoPago\Payments;

use App\Actions\Payments\UpsertPayment;
use App\DTOs\PaymentMethodDto;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\PaymentVendor;
use App\Events\Payments\PaymentCreated;
use App\Events\Payments\PaymentUpdated;
use App\Models\Order;
use App\Models\Subscription;
use App\Services\MercadoPago\Payment as PaymentService;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Throwable;

class RegisterModelPayment implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly string $paymentId,
        private readonly Order|Subscription $model
    ) {}

    public function middleware()
    {
        return [
            new WithoutOverlapping("register-model-payment:{$this->paymentId}:{$this->model->id}"),
        ];
    }

    /**
     * @throws Exception|Throwable
     */
    public function handle(PaymentService $paymentService, UpsertPayment $upsertPayment): void
    {
        $payment = $paymentService->get($this->model->application, $this->paymentId);

        $status = PaymentStatus::from(data_get($payment, 'status'));

        $paymentModel = $upsertPayment->handle(
            data_get($payment, 'id'),
            $this->model,
            data_get($payment, 'transaction_amount'),
            $status,
            PaymentVendor::MERCADOPAGO,
            $payment,
            paymentMethod: new PaymentMethodDto([
                'paymentMethod' => when(data_get($payment, 'payment_method_id') === 'account_money', PaymentMethod::MERCADOPAGO, PaymentMethod::CARD),
                'lastFourDigits' => data_get($payment, 'card.last_four_digits'),
                'paymentTypeId' => data_get($payment, 'payment_type_id'),
                'payment_method_id' => data_get($payment, 'payment_method_id'),
            ])
        );

        $event = $paymentModel->wasRecentlyCreated ? PaymentCreated::class : PaymentUpdated::class;

        event(new $event($paymentModel));
    }
}
