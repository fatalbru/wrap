<?php

declare(strict_types=1);

namespace App\Actions\Applications;

use App\Concerns\Action;
use App\Enums\Environment;
use App\Enums\PaymentVendor;
use App\Enums\ProductType;
use App\Models\Application;

class AssignApplication extends Action
{
    protected bool $shouldLock = false;

    public function handle(
        Environment $environment,
        PaymentVendor $paymentVendor,
        ProductType $productType,
    ): Application {
        return Application::query()
            ->where('vendor', $paymentVendor)
            ->where('environment', $environment)
            ->whereJsonContains('features', $productType)
            ->firstOrFail();
    }
}
