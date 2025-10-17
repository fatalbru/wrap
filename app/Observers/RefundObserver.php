<?php

namespace App\Observers;

use App\Events\Refunds\RefundCreated;
use App\Models\Refund;

class RefundObserver
{
    public function created(Refund $refund): void
    {
        event(new RefundCreated($refund));
    }
}
