<?php

namespace App\Listeners\Webhooks;

use App\Interfaces\OutgoingWebhookInterface;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class DispatchWebhook implements ShouldQueue
{
    /**
     * @throws ConnectionException
     */
    public function handle(OutgoingWebhookInterface $event): void
    {
        Http::withHeader('x-webhook-signature', config('mrr.webhook_signature'))
            ->throw()
            ->post(config('mrr.webhook_url'), [
                'event' => config('mrr.webhook_event_name')[get_class($event)],
                'timestamp' => now()->timestamp,
                'data' => $event->getWebhookData()
            ]);
    }
}
