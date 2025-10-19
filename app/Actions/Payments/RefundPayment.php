<?php

declare(strict_types=1);

namespace App\Actions\Payments;

use App\Concerns\Action;
use App\Enums\PaymentStatus;
use App\Models\Payment;
use App\Models\Refund;
use App\Services\MercadoPago\Payment as PaymentService;
use Illuminate\Support\Str;
use Throwable;

final class RefundPayment extends Action
{
    public function __construct(private readonly PaymentService $paymentService) {}

    /**
     * @throws Throwable
     */
    public function execute(Payment $payment): Refund
    {
        return $this->lock(function () use ($payment) {
            $idempotency = Str::random(128);

            $response = $this->paymentService->refund(
                $payment->payable->application,
                $payment->vendor_id,
                $idempotency
            );

            throw_if(
                data_get($response, 'status') !== 'approved',
                __('Refund could not be processed.')
            );

            $payment->update([
                'refunded_at' => now(),
                'status' => PaymentStatus::REFUNDED,
            ]);

            return $payment->refunds()->create([
                'amount' => $payment->amount,
                'vendor_id' => data_get($response, 'id'),
                'vendor_data' => $response,
            ]);
        }, ...func_get_args());
    }
}
