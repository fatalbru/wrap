<?php

namespace App\Listeners\Webhooks;

use App\Interfaces\OutgoingWebhookInterface;
use App\Jobs\Webhooks\SendWebhook;

class DispatchWebhook
{
    public function handle(OutgoingWebhookInterface $event): void
    {
        dispatch(new SendWebhook(
            config('wrap.webhook_event_name')[get_class($event)],
            $event->getModel(),
            $event->getWebhookData(),
        ));
    }
}
