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
/**
 * @property int $id
 * @property string $ksuid
 * @property int|null $application_id
 * @property int|null $customer_id
 * @property int $price_id
 * @property \Carbon\CarbonImmutable|null $started_at
 * @property \Carbon\CarbonImmutable|null $canceled_at
 * @property \Carbon\CarbonImmutable|null $next_payment_at
 * @property \Carbon\CarbonImmutable|null $trial_started_at
 * @property \Carbon\CarbonImmutable|null $trial_ended_at
 * @property SubscriptionStatus $status
 * @property string|null $vendor_id
 * @property PaymentVendor|null $vendor
 * @property array<array-key, mixed> $vendor_data
 * @property Environment $environment
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property-read \App\Models\Application|null $application
 * @property-read \App\Models\Checkout|null $checkout
 * @property-read \App\Models\Customer|null $customer
 * @property-read bool $cancelable
 * @property-read \App\Enums\PaymentMethod|null $payment_method
 * @property-read bool $trial
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Payment> $payments
 * @property-read int|null $payments_count
 * @property-read \App\Models\Price $price
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\WebhookLog> $webhookLogs
 * @property-read int|null $webhook_logs_count
 * @method static Builder<static>|Subscription byKsuid(string $ksuid)
 * @method static \Database\Factories\SubscriptionFactory factory($count = null, $state = [])
 * @method static Builder<static>|Subscription newModelQuery()
 * @method static Builder<static>|Subscription newQuery()
 * @method static Builder<static>|Subscription query()
 * @method static Builder<static>|Subscription trialEnded()
 * @method static Builder<static>|Subscription whereApplicationId($value)
 * @method static Builder<static>|Subscription whereCanceledAt($value)
 * @method static Builder<static>|Subscription whereCreatedAt($value)
 * @method static Builder<static>|Subscription whereCustomerId($value)
 * @method static Builder<static>|Subscription whereEnvironment($value)
 * @method static Builder<static>|Subscription whereId($value)
 * @method static Builder<static>|Subscription whereKsuid($value)
 * @method static Builder<static>|Subscription whereNextPaymentAt($value)
 * @method static Builder<static>|Subscription wherePriceId($value)
 * @method static Builder<static>|Subscription whereStartedAt($value)
 * @method static Builder<static>|Subscription whereStatus($value)
 * @method static Builder<static>|Subscription whereTrialEndedAt($value)
 * @method static Builder<static>|Subscription whereTrialStartedAt($value)
 * @method static Builder<static>|Subscription whereUpdatedAt($value)
 * @method static Builder<static>|Subscription whereVendor($value)
 * @method static Builder<static>|Subscription whereVendorData($value)
 * @method static Builder<static>|Subscription whereVendorId($value)
 * @mixin \Eloquent
 */
class Subscription extends Model
{
    use HasFactory, HasKsuid, HasWebhookLogs;

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
