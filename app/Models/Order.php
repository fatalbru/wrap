<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Environment;
use App\Enums\OrderStatus;
use App\Events\Orders\OrderCompleted;
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
class Order extends Model
{
    use HasFactory, HasKsuid, HasWebhookLogs;

    protected $fillable = ['status', 'vendor_data', 'vendor', 'vendor_id', 'price_id', 'completed_at', 'canceled_at',
        'amount', 'customer_id', 'decline_reason', 'data', 'paid_at', 'environment'];

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
            $this->update([
                'status' => OrderStatus::COMPLETED,
                'completed_at' => now(),
            ]);

            event(new OrderCompleted($this));
        }
    }
}
