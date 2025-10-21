<?php

namespace App\Jobs\MercadoPago\Payments;

use App\Enums\PaymentStatus;
use App\Enums\PaymentVendor;
use App\Events\Payments\PaymentCreated;
use App\Events\Payments\PaymentUpdated;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Subscription;
use App\Services\MercadoPago\Payment as PaymentService;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Middleware\WithoutOverlapping;

class RegisterModelPayment implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly string             $paymentId,
        private readonly Order|Subscription $model
    )
    {
    }

    public function middleware()
    {
        return [
            new WithoutOverlapping("register-model-payment:{$this->paymentId}:{$this->model->id}"),
        ];
    }

    /**
     * @throws Exception
     */
    public function handle(PaymentService $paymentService): void
    {
        $payment = $paymentService->get($this->model->application, $this->paymentId);

        $status = PaymentStatus::from(data_get($payment, 'status'));

        $paymentModel = Payment::query()->where('vendor_id', data_get($payment, 'id'))->firstOrNew();
        $paymentModel->customer()->associate($this->model->customer);
        $paymentModel->environment = $this->model->environment;
        $paymentModel->status = $status;
        $paymentModel->amount = data_get($payment, 'transaction_amount');
        $paymentModel->paid_at = $status === PaymentStatus::APPROVED ? now() : null;
        $paymentModel->vendor_data = $payment;
        $paymentModel->payment_vendor = PaymentVendor::MERCADOPAGO;
        $paymentModel->payment_method = data_get($payment, 'payment_method_id');
        $paymentModel->payment_type = data_get($payment, 'payment_type_id');
        $paymentModel->card_last_digits = data_get($payment, 'card.last_four_digits');
        $paymentModel->save();

        $event = $paymentModel->wasRecentlyCreated ? PaymentCreated::class : PaymentUpdated::class;

        event(new $event($paymentModel));
    }
}
