<?php

declare(strict_types=1);

namespace App\Observers;

use App\Actions\CreatePreapprovalPlan;
use App\Enums\ProductType;
use App\Models\Price;
use Throwable;

class PriceObserver
{
    /**
     * @throws Throwable
     */
    public function created(Price $price): void
    {
        if ($price->product->type === ProductType::SUBSCRIPTION) {
            app(CreatePreapprovalPlan::class)->handle($price);
        }
    }
}
