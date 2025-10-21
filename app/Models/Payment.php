<?php

declare(strict_types=1);

namespace App\Models;

use App\Casts\UseCents;
use App\Enums\Environment;
use App\Enums\PaymentStatus;
use App\Enums\PaymentVendor;
use App\Observers\PaymentObserver;
use App\Traits\HasKsuid;
use App\Traits\HasWebhookLogs;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

#[ObservedBy(PaymentObserver::class)]
class Payment extends Model
{
    use HasFactory, HasKsuid, HasWebhookLogs;

    protected static function boot()
    {
        parent::boot();

        self::creating(function (Payment $payment): void {
            $payment->application_id ??= $payment->payable->application_id;
        });
    }

    protected function casts()
    {
        return [
            'amount' => UseCents::class,
            'status' => PaymentStatus::class,
            'paid_at' => 'datetime',
            'refunded_at' => 'datetime',
            'vendor_data' => 'json',
            'environment' => Environment::class,
            'payment_vendor' => PaymentVendor::class,
        ];
    }

    public function payable(): MorphTo
    {
        return $this->morphTo();
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function refunds(): HasMany
    {
        return $this->hasMany(Refund::class);
    }

    public function isSuccessful(): bool
    {
        return $this->status === PaymentStatus::APPROVED;
    }

    public function scopeSearch(Builder $builder, ?string $search = null): void
    {
        $builder->when(filled($search), function (Builder $builder) use ($search): void {
            $builder->whereAny(['vendor_data', 'ksuid'], 'like', "%{$search}%")
                ->orWhereHas('customer', function (Builder $builder) use ($search): void {
                    $builder->search($search);
                });
        });
    }
}
