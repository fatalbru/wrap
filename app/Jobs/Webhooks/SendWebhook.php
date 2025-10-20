<?php

namespace App\Jobs\Webhooks;

use App\Enums\Environment;
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
        protected Model $model,
        protected array $payload
    ) {}

    public function handle(): void
    {
        /** @var Environment $environment */
        $environment = $this->model->environment;

        $webhookUrl = config('wrap.webhook_urls.'.$environment->value);

        $this->model->webhookLogs()->create([
            'type' => WebhookType::OUTGOING,
            'payload' => $this->payload,
            'event_name' => $this->eventName,
        ]);

        if (config('wrap.webhook_fake')) {
            Log::debug(__CLASS__, [
                'url' => $webhookUrl,
                'signature' => config('wrap.webhook_signature'),
                'payload' => [
                    'event' => $this->eventName,
                    'timestamp' => now()->timestamp,
                    'data' => $this->payload,
                ],
            ]);

            return;
        }

        Http::withHeader('x-webhook-signature', config('wrap.webhook_signature'))
            ->throw()
            ->post($webhookUrl, [
                'event' => $this->eventName,
                'timestamp' => now()->timestamp,
                'data' => $this->payload,
            ]);
    }
}
