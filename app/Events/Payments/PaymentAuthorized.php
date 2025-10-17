<?php

namespace App\Events\Payments;

use App\Interfaces\OutgoingWebhookInterface;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PaymentAuthorized implements OutgoingWebhookInterface
{
    use Dispatchable, SerializesModels;

    public function __construct(public readonly Payment $payment) {}

    public function getWebhookData(): array
    {
        return [];
    }

    public function getModel(): Model
    {
        return $this->payment;
    }
}
