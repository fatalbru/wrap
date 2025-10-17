<?php

namespace App\Models;

use App\Enums\PaymentProvider;
use App\Enums\WebhookType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class WebhookLog extends Model
{
    protected $fillable = [
        'application_id',
        'payload',
        'provider',
        'idempotency',
        'type',
        'event_name'
    ];

    protected static function boot()
    {
        parent::boot();

        self::creating(function (WebhookLog $webhookLog): void {
            $webhookLog->application_id ??= $webhookLog->loggable?->application_id;
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

    public function loggable(): MorphTo
    {
        return $this->morphTo();
    }
}
