<?php

namespace App\Models;

use App\Enums\PaymentProvider;
use App\Enums\WebhookType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

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
