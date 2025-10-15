<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Payment;
use App\PaymentStatus;
use App\Services\MercadoPago\Payment as PaymentService;
use Illuminate\Support\Str;

final readonly class RefundPayment
{
    public function __construct(private PaymentService $paymentService)
    {
    }

    public function handle(Payment $payment): void
    {
        $idempotency = Str::random(128);

        $this->paymentService->refund($payment->payable->application, $payment->vendor_id, $idempotency);

        $payment->update([
            'refunded_at' => now(),
            'status' => PaymentStatus::REFUNDED
        ]);

        $payment->refunds()->create([
            'amount' => $payment->amount,
        ]);
    }
}
