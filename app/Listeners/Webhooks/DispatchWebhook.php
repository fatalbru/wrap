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
                'event' => Str::of(get_class($event))
                    ->classBasename()
                    ->snake('.')
                    ->toString(),
                'timestamp' => now()->timestamp,
                'data' => $event->getWebhookData()
            ]);
    }
}
