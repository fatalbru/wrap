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

/**
 * @property int $id
 * @property string $ksuid
 * @property string $checkoutable_type
 * @property int $checkoutable_id
 * @property int $customer_id
 * @property string $redirect_url
 * @property \Carbon\CarbonImmutable|null $expires_at
 * @property string|null $canceled_at
 * @property \Carbon\CarbonImmutable|null $completed_at
 * @property int $min_installments
 * @property int $max_installments
 * @property Environment $environment
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property-read Model|\Eloquent $checkoutable
 * @property-read \App\Models\Customer $customer
 * @property-read bool $expired
 * @property-read float $total
 * @property-read ProductType $type
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Payment> $payments
 * @property-read int|null $payments_count
 * @method static Builder<static>|Checkout byKsuid(string $ksuid)
 * @method static \Database\Factories\CheckoutFactory factory($count = null, $state = [])
 * @method static Builder<static>|Checkout newModelQuery()
 * @method static Builder<static>|Checkout newQuery()
 * @method static Builder<static>|Checkout orders()
 * @method static Builder<static>|Checkout query()
 * @method static Builder<static>|Checkout whereCanceledAt($value)
 * @method static Builder<static>|Checkout whereCheckoutableId($value)
 * @method static Builder<static>|Checkout whereCheckoutableType($value)
 * @method static Builder<static>|Checkout whereCompletedAt($value)
 * @method static Builder<static>|Checkout whereCreatedAt($value)
 * @method static Builder<static>|Checkout whereCustomerId($value)
 * @method static Builder<static>|Checkout whereEnvironment($value)
 * @method static Builder<static>|Checkout whereExpiresAt($value)
 * @method static Builder<static>|Checkout whereId($value)
 * @method static Builder<static>|Checkout whereKsuid($value)
 * @method static Builder<static>|Checkout whereMaxInstallments($value)
 * @method static Builder<static>|Checkout whereMinInstallments($value)
 * @method static Builder<static>|Checkout whereRedirectUrl($value)
 * @method static Builder<static>|Checkout whereUpdatedAt($value)
 * @mixin \Eloquent
 */
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
