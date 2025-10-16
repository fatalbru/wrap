<?php

namespace App\Models;

use App\PaymentProvider;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class WebhookLog extends Model
{
    protected $fillable = ['application_id', 'payload', 'provider', 'idempotency'];

    protected static function boot()
    {
        parent::boot();

        self::creating(function (WebhookLog $webhookLog) {
            $webhookLog->application_id ??= $webhookLog->loggable?->application_id;
        });
    }

    protected function casts()
    {
        return [
            'provider' => PaymentProvider::class,
            'payload' => 'json',
        ];
    }

    function loggable(): MorphTo
    {
        return $this->morphTo();
    }
}
