<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Order;
use App\Models\Subscription;
use Illuminate\Database\Eloquent\Collection;

final readonly class ListPayments
{
    public function __construct() {}

    public function handle(Subscription|Order $model): Collection
    {
        return $model->payments()->latest('id')->get();
    }
}
