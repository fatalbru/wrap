<?php

namespace App\Events\MercadoPago;

use App\Models\Order;
use App\Models\Subscription;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WebhookReceived
{
    use Dispatchable, SerializesModels;

    public function __construct(
        protected readonly array $payload,
        protected readonly Order|Subscription|null $relatedModel = null,
    ) {}

    public function getAction(): ?string
    {
        return data_get($this->payload, 'action');
    }

    public function getType(): ?string
    {
        return data_get($this->payload, 'type');
    }

    public function getTopic(): ?string
    {
        return data_get($this->payload, 'topic');
    }

    public function getPayload(): array
    {
        return $this->payload;
    }

    public function getRelatedModel(): Order|Subscription|null
    {
        return $this->relatedModel;
    }
}
