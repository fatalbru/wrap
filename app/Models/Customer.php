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
class Customer extends Model
{
    use HasFactory, HasKsuid, HasWebhookLogs;

    protected $fillable = ['name', 'email', 'environment'];

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
