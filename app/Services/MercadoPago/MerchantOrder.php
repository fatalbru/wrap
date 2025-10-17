<?php

declare(strict_types=1);

namespace App\Services\MercadoPago;

use App\Models\Application;
use Illuminate\Support\Facades\Http;

final class MerchantOrder
{
    public function get(Application $application, int $merchantOrderId): array
    {
        return Http::mercadopago($application)
            ->throw()
            ->get("/merchant_orders/{$merchantOrderId}")
            ->json();
    }
}
