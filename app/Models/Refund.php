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
/**
 * @property int $id
 * @property string $ksuid
 * @property int $payment_id
 * @property float $amount
 * @property string|null $vendor_id
 * @property string $vendor_data
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property-read \App\Models\Payment $payment
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\WebhookLog> $webhookLogs
 * @property-read int|null $webhook_logs_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Refund byKsuid(string $ksuid)
 * @method static \Database\Factories\RefundFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Refund newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Refund newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Refund query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Refund whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Refund whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Refund whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Refund whereKsuid($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Refund wherePaymentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Refund whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Refund whereVendorData($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Refund whereVendorId($value)
 * @mixin \Eloquent
 */
class Refund extends Model
{
    use HasFactory, HasKsuid, HasWebhookLogs;

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }
}
