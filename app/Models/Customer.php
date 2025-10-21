<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Environment;
use App\Observers\CustomerObserver;
use App\Traits\HasKsuid;
use App\Traits\HasWebhookLogs;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

#[ObservedBy(CustomerObserver::class)]
/**
 * @property int $id
 * @property string $portal_id
 * @property string $ksuid
 * @property string $name
 * @property string $email
 * @property Environment $environment
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Order> $orders
 * @property-read int|null $orders_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Payment> $payments
 * @property-read int|null $payments_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Subscription> $subscriptions
 * @property-read int|null $subscriptions_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\WebhookLog> $webhookLogs
 * @property-read int|null $webhook_logs_count
 * @method static Builder<static>|Customer byKsuid(string $ksuid)
 * @method static \Database\Factories\CustomerFactory factory($count = null, $state = [])
 * @method static Builder<static>|Customer newModelQuery()
 * @method static Builder<static>|Customer newQuery()
 * @method static Builder<static>|Customer query()
 * @method static Builder<static>|Customer search(?string $search)
 * @method static Builder<static>|Customer whereCreatedAt($value)
 * @method static Builder<static>|Customer whereEmail($value)
 * @method static Builder<static>|Customer whereEnvironment($value)
 * @method static Builder<static>|Customer whereId($value)
 * @method static Builder<static>|Customer whereKsuid($value)
 * @method static Builder<static>|Customer whereName($value)
 * @method static Builder<static>|Customer wherePortalId($value)
 * @method static Builder<static>|Customer whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Customer extends Model
{
    use HasFactory, HasKsuid, HasWebhookLogs;

    protected static function boot()
    {
        parent::boot();

        self::creating(function (Customer $customer): void {
            $customer->portal_id = self::assignPortalId();
        });
    }

    protected function casts()
    {
        return [
            'environment' => Environment::class,
        ];
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public static function assignPortalId(): string
    {
        do {
            $ksuid = Str::random(32);
        } while (self::where('portal_id', $ksuid)->exists());

        return $ksuid;
    }

    public function scopeSearch(Builder $builder, ?string $search): void
    {
        $builder->when(filled($search), function (Builder $builder) use ($search): void {
            $builder->whereAny(['name', 'email', 'ksuid'], 'like', "%{$search}%");
        });
    }
}
