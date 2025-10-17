<?php

declare(strict_types=1);

namespace App\Models;

use App\Casts\UseCents;
use App\Enums\Environment;
use App\Enums\FrequencyType;
use App\Enums\PaymentVendor;
use App\Enums\ProductType;
use App\Observers\PriceObserver;
use App\Traits\HasKsuid;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[ObservedBy(PriceObserver::class)]
class Price extends Model
{
    use HasFactory, HasKsuid;

    protected $fillable = ['name', 'frequency', 'price', 'trial_days', 'vendor_id', 'vendor', 'environment'];

    protected function casts()
    {
        return [
            'price' => UseCents::class,
            'frequency' => FrequencyType::class,
            'vendor' => PaymentVendor::class,
            'environment' => Environment::class,
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function getHasTrialAttribute(): bool
    {
        return $this->product->type === ProductType::SUBSCRIPTION && $this->trial_days > 0;
    }
}
