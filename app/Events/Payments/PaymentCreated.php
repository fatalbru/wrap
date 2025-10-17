<?php

namespace App\Events\Payments;

use App\Interfaces\OutgoingWebhookInterface;
use App\Models\Payment;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PaymentCreated implements OutgoingWebhookInterface
{
    use Dispatchable, SerializesModels;

    public function __construct(public readonly Payment $payment)
    {
    }

    function getWebhookData(): array
    {
        return [];
    }
}
