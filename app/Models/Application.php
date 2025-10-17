<?php

namespace App\Models;

use App\Enums\Environment;
use App\Enums\PaymentVendor;
use App\Enums\ProductType;
use Illuminate\Database\Eloquent\Model;

class Application extends Model
{
    protected $fillable = ['vendor', 'public_key', 'private_key', 'features', 'name',
        'environment', 'vendor_id', 'vendor_secondary_id'];

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
