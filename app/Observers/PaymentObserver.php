<?php

namespace App\Observers;

use App\Events\Payments\PaymentAuthorized;
use App\Events\Payments\PaymentCreated;
use App\Events\Payments\PaymentFailed;
use App\Events\Payments\PaymentUpdated;
use App\Models\Payment;

class PaymentObserver
{
    public function created(Payment $payment): void
    {
        event(new PaymentCreated($payment));

        if ($payment->isSuccessful()) {
            event(new PaymentAuthorized($payment));
        } else {
            event(new PaymentFailed($payment));
        }
    }

    public function updated(Payment $payment): void
    {
        event(new PaymentUpdated($payment));
    }
}
