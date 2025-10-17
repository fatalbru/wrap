<?php

namespace App\Jobs\Webhooks;

use App\Enums\WebhookType;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SendWebhook implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected string $eventName,
        protected Model  $model,
        protected array  $payload
    )
    {
    }

    public function handle(): void
    {
        $this->model->webhookLogs()->create([
            'type' => WebhookType::OUTGOING,
            'payload' => $this->payload,
            'event_name' => $this->eventName,
        ]);

        if (config('mrr.webhook_fake')) {
            Log::debug(__CLASS__, [
                'url' => config('mrr.webhook_url'),
                'signature' => config('mrr.webhook_signature'),
                'payload' => [
                    'event' => $this->eventName,
                    'timestamp' => now()->timestamp,
                    'data' => $this->payload,
                ],
            ]);

            return;
        }

        Http::withHeader('x-webhook-signature', config('mrr.webhook_signature'))
            ->throw()
            ->post(config('mrr.webhook_url'), [
                'event' => $this->eventName,
                'timestamp' => now()->timestamp,
                'data' => $this->payload,
            ]);
    }
}
