<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Environment;
use App\Enums\OrderStatus;
use App\Observers\OrderObserver;
use App\Traits\HasKsuid;
use App\Traits\HasWebhookLogs;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

#[ObservedBy(OrderObserver::class)]
/**
 * @property int $id
 * @property string $ksuid
 * @property int|null $application_id
 * @property int $customer_id
 * @property OrderStatus $status
 * @property string|null $vendor_id
 * @property string|null $vendor
 * @property array<array-key, mixed> $vendor_data
 * @property \Carbon\CarbonImmutable|null $completed_at
 * @property \Carbon\CarbonImmutable|null $canceled_at
 * @property Environment $environment
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property-read \App\Models\Application|null $application
 * @property-read \App\Models\Checkout|null $checkout
 * @property-read \App\Models\Customer $customer
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\OrderItem> $items
 * @property-read int|null $items_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Payment> $payments
 * @property-read int|null $payments_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\WebhookLog> $webhookLogs
 * @property-read int|null $webhook_logs_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order byKsuid(string $ksuid)
 * @method static \Database\Factories\OrderFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereApplicationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereCanceledAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereCompletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereCustomerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereEnvironment($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereKsuid($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereVendor($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereVendorData($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order whereVendorId($value)
 * @mixin \Eloquent
 */
class Order extends Model
{
    use HasFactory, HasKsuid, HasWebhookLogs;

    protected static function boot()
    {
        parent::boot();

        self::creating(function (Order $order): void {
            $order->vendor_data ??= [];
        });
    }

    protected function casts()
    {
        return [
            'status' => OrderStatus::class,
            'completed_at' => 'datetime',
            'canceled_at' => 'datetime',
            'vendor_data' => 'array',
            'environment' => Environment::class,
        ];
    }

    public function checkout(): MorphOne
    {
        return $this->morphOne(Checkout::class, 'checkoutable');
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function payments(): MorphMany
    {
        return $this->morphMany(Payment::class, 'payable');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function complete(): void
    {
        if (blank($this->completed_at)) {
            $this->status = OrderStatus::COMPLETED;
            $this->completed_at = now();
            $this->save();
        }
    }
}
