<?php

declare(strict_types=1);

namespace App\Services\MercadoPago;

use App\Models\Application;
use Illuminate\Support\Facades\Http;

final class Preapproval
{
    public function create(Application $application, array $payload): array
    {
        return Http::mercadopago($application)
            ->throw()
            ->post("preapproval_plan", $payload)
            ->json();
    }
}
