<?php

namespace App\Models;

use App\Enums\PaymentProvider;
use App\Enums\WebhookType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property int $id
 * @property int|null $application_id
 * @property string|null $loggable_type
 * @property int|null $loggable_id
 * @property array<array-key, mixed> $payload
 * @property PaymentProvider|null $provider
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property string|null $idempotency
 * @property WebhookType $type
 * @property string|null $event_name
 * @property-read \App\Models\Application|null $application
 * @property-read Model|\Eloquent|null $loggable
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WebhookLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WebhookLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WebhookLog query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WebhookLog whereApplicationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WebhookLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WebhookLog whereEventName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WebhookLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WebhookLog whereIdempotency($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WebhookLog whereLoggableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WebhookLog whereLoggableType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WebhookLog wherePayload($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WebhookLog whereProvider($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WebhookLog whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WebhookLog whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class WebhookLog extends Model
{
    protected static function boot()
    {
        parent::boot();

        self::creating(function (WebhookLog $webhookLog): void {
            $webhookLog->application_id ??= $webhookLog->loggable?->application_id ?? null;
        });
    }

    protected function casts()
    {
        return [
            'provider' => PaymentProvider::class,
            'type' => WebhookType::class,
            'payload' => 'json',
        ];
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function loggable(): MorphTo
    {
        return $this->morphTo();
    }
}
