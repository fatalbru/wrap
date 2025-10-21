<?php

declare(strict_types=1);

namespace App\Models;

use App\Observers\RefundObserver;
use App\Traits\HasKsuid;
use App\Traits\HasWebhookLogs;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[ObservedBy(RefundObserver::class)]
class Refund extends Model
{
    use HasFactory, HasKsuid, HasWebhookLogs;

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }
}
