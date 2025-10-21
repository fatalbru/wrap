<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Environment;
use App\Enums\ProductType;
use App\Traits\HasKsuid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Checkout extends Model
{
    use HasFactory, HasKsuid;

    protected $with = [
        'payments',
    ];

    protected static function boot()
    {
        parent::boot();

        self::creating(function (Checkout $checkout): void {
            $checkout->redirect_url ??= '';
        });
    }

    protected function casts()
    {
        return [
            'completed_at' => 'datetime',
            'expires_at' => 'datetime',
            'environment' => Environment::class,
        ];
    }

    public function checkoutable(): MorphTo
    {
        return $this->morphTo();
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function payments(): MorphMany
    {
        return $this->morphMany(Payment::class, 'payable');
    }

    public function getTypeAttribute(): ProductType
    {
        return $this->checkoutable instanceof Subscription ? ProductType::SUBSCRIPTION : ProductType::ORDER;
    }

    public function getExpiredAttribute(): bool
    {
        return filled($this->expires_at) && $this->expires_at->isPast();
    }

    public function getTotalAttribute(): float
    {
        if ($this->checkoutable instanceof Subscription) {
            return $this->checkoutable->price->price;
        }

        return $this->checkoutable
            ->items
            ->map(fn (OrderItem $orderItem) => $orderItem->quantity * $orderItem->price->price)
            ->sum();
    }

    public function scopeOrders(Builder $builder): void
    {
        $builder->whereMorphedTo('checkoutable', Order::class);
    }

    public static function getKsuidPrefix(): string
    {
        return 'ch';
    }

    public function complete(): void
    {
        if (blank($this->completed_at)) {
            $this->completed_at = now();
            $this->save();
        }
    }
}
