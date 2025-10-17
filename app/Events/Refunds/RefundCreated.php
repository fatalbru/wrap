<?php

namespace App\Events\Refunds;

use App\Interfaces\OutgoingWebhookInterface;
use App\Models\Refund;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RefundCreated implements OutgoingWebhookInterface
{
    use Dispatchable, SerializesModels;

    public function __construct(public readonly Refund $refund) {}

    public function getWebhookData(): array
    {
        return [];
    }

    public function getModel(): Model
    {
        return $this->refund;
    }
}
