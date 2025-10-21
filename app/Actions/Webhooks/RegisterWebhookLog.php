<?php

declare(strict_types=1);

namespace App\Actions\Webhooks;

use App\Concerns\Action;
use App\Enums\PaymentProvider;
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
        ?PaymentProvider $paymentProvider = null
    ): void
    {
        throw_if(!method_exists($model, 'webhookLogs'), 'Model does not support webhook logs');

        throw_if(
            filled($idempotency) && $model->webhookLogs()->where('idempotency', $idempotency)->exists(),
            IdempotencyOverlap::class
        );

        $this->lock(function () use ($model, $idempotency, $paymentProvider, $payload): void {
            $webhookLog = new WebhookLog();
            $webhookLog->loggable()->associate($model);
            $webhookLog->payload = $payload;
            $webhookLog->idempotency = $idempotency;
            $webhookLog->provider = $paymentProvider;
            $webhookLog->save();
        }, ...func_get_args());
    }
}
