<?php

declare(strict_types=1);

namespace App\Actions\Payments;

use App\Concerns\Action;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Subscription;
use Illuminate\Database\Eloquent\Collection;
use Throwable;

final class ListPayments extends Action
{
    /**
     * @throws Throwable
     */
    public function execute(Subscription|Order $model): Collection
    {
        throw_if(! method_exists($model, 'payments'), 'Model is not payable.');

        return Payment::query()->whereBelongsTo($model, 'payable')->latest('id')->get();
    }
}
