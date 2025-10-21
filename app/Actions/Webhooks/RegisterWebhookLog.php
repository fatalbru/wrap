<?php

declare(strict_types=1);

namespace App\Actions\Webhooks;

use App\Concerns\Action;
use App\Enums\PaymentProvider;
use App\Enums\WebhookType;
use App\Exceptions\IdempotencyOverlap;
use App\Models\WebhookLog;
use Illuminate\Database\Eloquent\Model;
use Throwable;

final class RegisterWebhookLog extends Action
{
    /**
     * @throws IdempotencyOverlap
     * @throws Throwable
     */
    public function execute(
        Model            $model,
        array|object     $payload,
        ?string          $idempotency = null,
        ?PaymentProvider $paymentProvider = null,
        ?string          $eventName = null,
        WebhookType      $webhookType = WebhookType::INCOMING,
    ): void
    {
        throw_if(!method_exists($model, 'webhookLogs'), 'Model does not support webhook logs');

        throw_if(
            filled($idempotency) && $model->webhookLogs()->where('idempotency', $idempotency)->exists(),
            IdempotencyOverlap::class
        );

        $this->lock(function () use ($model, $idempotency, $paymentProvider, $payload, $webhookType, $eventName): void {
            $webhookLog = new WebhookLog();
            $webhookLog->loggable()->associate($model);
            $webhookLog->payload = $payload;
            $webhookLog->idempotency = $idempotency;
            $webhookLog->provider = $paymentProvider;
            $webhookLog->event_name = $eventName;
            $webhookLog->type = $webhookType;
            $webhookLog->save();
        }, ...func_get_args());
    }
}
