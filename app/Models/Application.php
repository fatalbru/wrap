<?php

namespace App\Models;

use App\Enums\Environment;
use App\Enums\PaymentVendor;
use App\Enums\ProductType;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property Environment $environment
 * @property string $name
 * @property string|null $vendor_secondary_id
 * @property string|null $vendor_id
 * @property PaymentVendor $vendor
 * @property string $public_key
 * @property string $private_key
 * @property array<array-key, mixed> $features
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Application newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Application newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Application query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Application whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Application whereEnvironment($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Application whereFeatures($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Application whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Application whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Application wherePrivateKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Application wherePublicKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Application whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Application whereVendor($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Application whereVendorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Application whereVendorSecondaryId($value)
 * @mixin \Eloquent
 */
class Application extends Model
{
    protected function casts()
    {
        return [
            'vendor' => PaymentVendor::class,
            'environment' => Environment::class,
            'public_key' => 'encrypted',
            'private_key' => 'encrypted',
            'features' => 'array',
        ];
    }

    public static function assign(PaymentVendor $paymentVendor, Environment $environment, ProductType $productType)
    {
        return Application::where('vendor', $paymentVendor)
            ->where('environment', $environment)
            ->whereJsonContains('features', $productType)
            ->firstOrFail();
    }
}
