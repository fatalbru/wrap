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
/**
 * @property int $id
 * @property int $application_id
 * @property string $ksuid
 * @property string $payable_type
 * @property int $payable_id
 * @property int $customer_id
 * @property mixed $amount
 * @property PaymentStatus $status
 * @property \Carbon\CarbonImmutable|null $paid_at
 * @property \Carbon\CarbonImmutable|null $refunded_at
 * @property string|null $decline_reason
 * @property string|null $vendor_id
 * @property array<array-key, mixed> $vendor_data
 * @property Environment $environment
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property string|null $payment_method
 * @property string|null $payment_type
 * @property string|null $card_last_digits
 * @property PaymentVendor $payment_vendor
 * @property-read \App\Models\Customer $customer
 * @property-read Model|\Eloquent $payable
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Refund> $refunds
 * @property-read int|null $refunds_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\WebhookLog> $webhookLogs
 * @property-read int|null $webhook_logs_count
 * @method static Builder<static>|Payment byKsuid(string $ksuid)
 * @method static \Database\Factories\PaymentFactory factory($count = null, $state = [])
 * @method static Builder<static>|Payment newModelQuery()
 * @method static Builder<static>|Payment newQuery()
 * @method static Builder<static>|Payment query()
 * @method static Builder<static>|Payment search(?string $search = null)
 * @method static Builder<static>|Payment whereAmount($value)
 * @method static Builder<static>|Payment whereApplicationId($value)
 * @method static Builder<static>|Payment whereCardLastDigits($value)
 * @method static Builder<static>|Payment whereCreatedAt($value)
 * @method static Builder<static>|Payment whereCustomerId($value)
 * @method static Builder<static>|Payment whereDeclineReason($value)
 * @method static Builder<static>|Payment whereEnvironment($value)
 * @method static Builder<static>|Payment whereId($value)
 * @method static Builder<static>|Payment whereKsuid($value)
 * @method static Builder<static>|Payment wherePaidAt($value)
 * @method static Builder<static>|Payment wherePayableId($value)
 * @method static Builder<static>|Payment wherePayableType($value)
 * @method static Builder<static>|Payment wherePaymentMethod($value)
 * @method static Builder<static>|Payment wherePaymentType($value)
 * @method static Builder<static>|Payment wherePaymentVendor($value)
 * @method static Builder<static>|Payment whereRefundedAt($value)
 * @method static Builder<static>|Payment whereStatus($value)
 * @method static Builder<static>|Payment whereUpdatedAt($value)
 * @method static Builder<static>|Payment whereVendorData($value)
 * @method static Builder<static>|Payment whereVendorId($value)
 * @mixin \Eloquent
 */
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
