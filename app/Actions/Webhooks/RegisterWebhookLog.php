<?php

declare(strict_types=1);

namespace App\Actions\Webhooks;

use App\Concerns\Action;
use App\Enums\PaymentProvider;
use App\Exceptions\IdempotencyOverlap;
use Illuminate\Database\Eloquent\Model;
use Throwable;

final class RegisterWebhookLog extends Action
{
    /**
     * @throws IdempotencyOverlap
     * @throws Throwable
     */
    public function handle(
        Model $model,
        array|object $payload,
        ?string $idempotency = null,
        ?PaymentProvider $paymentProvider = null
    ): void {
        throw_if(! method_exists($model, 'webhookLogs'), 'Model does not support webhook logs');

        if (filled($idempotency) && $model->webhookLogs()->where('idempotency', $idempotency)->exists()) {
            throw new IdempotencyOverlap;
        }

        $this->lock(function () use ($model, $idempotency, $paymentProvider, $payload): void {
            $model->webhookLogs()->create([
                'payload' => $payload,
                'idempotency' => $idempotency,
                'provider' => $paymentProvider,
            ]);
        }, ...func_get_args());
    }
}
