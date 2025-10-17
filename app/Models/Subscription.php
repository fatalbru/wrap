<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Environment;
use App\Enums\PaymentMethod;
use App\Enums\PaymentVendor;
use App\Enums\SubscriptionStatus;
use App\Observers\SubscriptionObserver;
use App\Traits\HasKsuid;
use App\Traits\HasWebhookLogs;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

#[ObservedBy(SubscriptionObserver::class)]
class Subscription extends Model
{
    use HasFactory, HasKsuid, HasWebhookLogs;

    protected $fillable = ['status', 'started_at', 'ended_at', 'next_payment_at', 'vendor_data',
        'canceled_at', 'vendor', 'vendor_id', 'price_id', 'environment', 'trial_started_at',
        'trial_ended_at', 'customer_id'];

    protected function casts()
    {
        return [
            'vendor' => PaymentVendor::class,
            'status' => SubscriptionStatus::class,
            'started_at' => 'datetime',
            'ended_at' => 'datetime',
            'next_payment_at' => 'datetime',
            'canceled_at' => 'datetime',
            'current_period_start' => 'datetime',
            'current_period_end' => 'datetime',
            'trial_started_at' => 'datetime',
            'trial_ended_at' => 'datetime',
            'vendor_data' => 'array',
            'environment' => Environment::class,
        ];
    }

    protected static function boot()
    {
        parent::boot();

        self::creating(function (Subscription $subscription): void {
            $subscription->vendor_data ??= [];
        });
    }

    public function checkout(): MorphOne
    {
        return $this->morphOne(Checkout::class, 'checkoutable');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function payments(): MorphMany
    {
        return $this->morphMany(Payment::class, 'payable');
    }

    public function price(): BelongsTo
    {
        return $this->belongsTo(Price::class);
    }

    public function getTrialAttribute(): bool
    {
        return filled($this->trial_started_at) &&
            filled($this->trial_ended_at) &&
            now()->betweenIncluded($this->trial_started_at, $this->trial_ended_at);
    }

    public function getPaymentMethodAttribute(): ?PaymentMethod
    {
        return PaymentMethod::parse(data_get($this->payments()->latest()->first(), 'vendor_data.payment_method_id'));
    }

    public function getCancelableAttribute(): bool
    {
        return in_array($this->status, [SubscriptionStatus::AUTHORIZED, SubscriptionStatus::PAUSED]);
    }

    public function scopeTrialEnded(Builder $builder): void
    {
        $builder->whereNotNull('trial_ended_at')->wherePast('trial_ended_at');
    }
}
