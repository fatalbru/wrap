<?php

declare(strict_types=1);

namespace App\Actions\Payments;

use App\Concerns\Action;
use App\DTOs\PaymentMethodDto;
use App\Enums\PaymentStatus;
use App\Enums\PaymentVendor;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Subscription;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Throwable;

final class CreatePayment extends Action
{
    /**
     * @throws LockTimeoutException
     * @throws Throwable
     */
    public function execute(
        Customer           $customer,
        Order|Subscription $payable,
        int|float          $amount,
        PaymentStatus      $status,
        PaymentVendor      $vendor,
        array              $vendorData = [],
        ?string            $declineReason = null,
        ?PaymentMethodDto  $paymentMethod = null,
    ): Payment
    {
        return $this->lock(fn() => $payable->payments()->create([
            'customer_id' => $customer->id,
            'amount' => $amount,
            'status' => $status,
            'decline_reason' => $declineReason,
            'vendor_data' => $vendorData,
            'payment_vendor' => $vendor,
            'payment_method' => $paymentMethod?->paymentMethodId,
            'payment_type' => $paymentMethod?->paymentTypeId,
            'card_last_digits' => $paymentMethod?->lastFourDigits,
        ]), ...func_get_args());
    }
}
