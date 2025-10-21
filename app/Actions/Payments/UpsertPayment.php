<?php

declare(strict_types=1);

namespace App\Actions\Payments;

use App\Concerns\Action;
use App\DTOs\PaymentMethodDto;
use App\Enums\PaymentStatus;
use App\Enums\PaymentVendor;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Subscription;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Throwable;

final class UpsertPayment extends Action
{
    /**
     * @throws LockTimeoutException
     * @throws Throwable
     */
    public function handle(
        string $vendorId,
        Order|Subscription $payable,
        int|float $amount,
        PaymentStatus $status,
        PaymentVendor $vendor,
        array $vendorData = [],
        ?string $declineReason = null,
        ?PaymentMethodDto $paymentMethod = null,
        ?CarbonImmutable $paidAt = null,
    ): Payment {
        return $this->lock(function () use ($payable, $amount, $status, $vendor, $vendorData, $declineReason, $paymentMethod, $paidAt, $vendorId) {
            $payment = Payment::query()->where('vendor_id', $vendorId)->firstOrNew();
            $payment->vendor_id = $vendorId;
            $payment->customer()->associate($payable->customer);
            $payment->amount = $amount;
            $payment->status = $status;
            $payment->decline_reason = $declineReason;
            $payment->vendor_data = $vendorData;
            $payment->payment_vendor = $vendor;
            $payment->payment_method = $paymentMethod?->paymentMethodId;
            $payment->payment_type = $paymentMethod?->paymentTypeId;
            $payment->card_last_digits = $paymentMethod?->lastFourDigits;
            $payment->paid_at = $paidAt;
            $payment->save();

            return $payment;
        }, ...func_get_args());
    }
}
