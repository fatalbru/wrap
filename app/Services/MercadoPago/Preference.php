<?php

declare(strict_types=1);

namespace App\Services\MercadoPago;

use App\Models\Application;
use Illuminate\Support\Facades\Http;

final class Preference
{
    public function get(Application $application, string $preferenceId): array
    {
        return Http::mercadopago($application)
            ->throw()
            ->get("/checkout/preferences/{$preferenceId}")
            ->json();
    }

    public function create(
        Application $application,
        array $items,
        string $externalReference,
        string $backUrl,
        string $notificationUrl,
    ) {
        return Http::mercadopago($application)
            ->throw()
            ->post('/checkout/preferences', [
                'items' => $items,
                'back_urls' => [
                    'success' => $backUrl,
                    'failure' => $backUrl,
                    'pending' => $backUrl,
                ],
                'notification_url' => $notificationUrl,
                'external_reference' => $externalReference,
            ])
            ->json();
    }
}
