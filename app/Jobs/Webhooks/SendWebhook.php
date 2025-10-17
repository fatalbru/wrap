<?php

namespace App\Jobs\Webhooks;

use App\Enums\WebhookType;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;

class SendWebhook implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected string $eventName,
        protected Model $model,
        protected array $payload
    ) {}

    public function handle(): void
    {
        $this->model->webhookLogs()->create([
            'type' => WebhookType::OUTGOING,
            'payload' => $this->payload,
            'event_name' => $this->eventName,
        ]);

        Http::withHeader('x-webhook-signature', config('mrr.webhook_signature'))
            ->throw()
            ->post(config('mrr.webhook_url'), [
                'event' => $this->eventName,
                'timestamp' => now()->timestamp,
                'data' => $this->payload,
            ]);
    }
}
