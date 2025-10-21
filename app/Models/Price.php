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

/**
 * @property int $id
 * @property string $ksuid
 * @property int $product_id
 * @property string $name
 * @property int|null $trial_days
 * @property FrequencyType|null $frequency
 * @property mixed $price
 * @property Environment $environment
 * @property string|null $vendor_id
 * @property PaymentVendor|null $vendor
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property-read bool $has_trial
 * @property-read \App\Models\Product $product
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Price byKsuid(string $ksuid)
 * @method static \Database\Factories\PriceFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Price newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Price newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Price query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Price whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Price whereEnvironment($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Price whereFrequency($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Price whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Price whereKsuid($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Price whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Price wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Price whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Price whereTrialDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Price whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Price whereVendor($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Price whereVendorId($value)
 * @mixin \Eloquent
 */
class Price extends Model
{
    use HasFactory, HasKsuid;

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
