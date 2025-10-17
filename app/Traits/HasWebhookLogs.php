<?php

declare(strict_types=1);

namespace App\Traits;

use App\Models\WebhookLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * @mixin Model
 */
trait HasWebhookLogs
{
    public function webhookLogs(): MorphMany
    {
        return $this->morphMany(WebhookLog::class, 'loggable');
    }
}
