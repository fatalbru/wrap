<?php

namespace App\Events\Refunds;

use App\Interfaces\OutgoingWebhookInterface;
use App\Models\Refund;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RefundCreated implements OutgoingWebhookInterface
{
    use Dispatchable, SerializesModels;

    public function __construct(public readonly Refund $refund)
    {
    }

    function getWebhookData(): array
    {
        return [];
    }
}
