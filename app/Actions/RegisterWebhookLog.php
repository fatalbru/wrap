<?php

declare(strict_types=1);

namespace App\Actions;

use App\Exceptions\IdempotencyOverlap;
use App\PaymentProvider;
use Illuminate\Database\Eloquent\Model;

final readonly class RegisterWebhookLog
{
    /**
     * @throws IdempotencyOverlap
     */
    public function handle(
        Model            $model,
        array|object     $payload,
        ?string          $idempotency = null,
        ?PaymentProvider $paymentProvider = null
    ): void
    {
        if (filled($idempotency) && $model->webhookLogs()->where('idempotency', $idempotency)->exists()) {
            throw new IdempotencyOverlap;
        }

        $model->webhookLogs()->create([
            'payload' => $payload,
            'idempotency' => $idempotency,
            'provider' => $paymentProvider,
        ]);
    }
}
