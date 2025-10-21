<?php

declare(strict_types=1);

namespace App\Services\MercadoPago;

use App\DTOs\PaymentMethodDto;
use App\Enums\Currency;
use App\Enums\SubscriptionStatus;
use App\Models\Application;
use App\Models\Price;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use SensitiveParameter;
use Throwable;

final class Subscription
{
    public function get(Application $application, string $id): ?array
    {
        return Http::mercadopago($application)
            ->throw()
            ->get("/preapproval/{$id}")
            ->json();
    }

    public function createPreapprovalPlan(Application $application, array $data): ?array
    {
        return Http::mercadopago($application)
            ->throw()
            ->post('/preapproval_plan', $data)
            ->json();
    }

    /**
     * @throws Throwable
     */
    public function subscribe(
        Application                             $application,
        Price                                   $price,
        string                                  $payerEmail,
        Currency                                $currency,
        string                                  $externalReference,
        #[SensitiveParameter] ?PaymentMethodDto $paymentMethod = null,
        ?string                                 $backUrl = null,
        array                                   $metadata = [],
        ?string                                 $notificationUrl = null,
        ?array                                  $preapproval = null
    ): ?array
    {
        Log::debug(__CLASS__, func_get_args());

        $payload = [
//            'metadata' => $metadata,
            'preapproval_plan_id' => data_get($preapproval, 'id'),
            'reason' => implode(' ', Arr::whereNotNull([$price->name, ...$metadata])),
            'external_reference' => $externalReference,
            'payer_email' => $payerEmail,
            'back_url' => $backUrl,
            'status' => 'pending',
            'card_token_id' => $paymentMethod?->token,
            'payment_method_id' => $paymentMethod?->payment_method_id,
            'payment_type_id' => $paymentMethod?->paymentTypeId,
            'auto_return' => 'all',
            'auto_recurring' => [
                'transaction_amount' => $price->price,
                'frequency' => $price->frequency->getFrequencyIterations(),
                'frequency_type' => $price->frequency->getFrequencyApiType(),
                'currency_id' => $currency->value,
            ],
            'excluded_payment_methods' => array(
            ),
            'excluded_payment_types' => array(
            ),
        ];

        if (filled($notificationUrl)) {
            $payload['notification_url'] = $notificationUrl;
        }

        if ($price->trial_days > 0) {
            Arr::set($payload, 'auto_recurring.free_trial', [
                'frequency' => $price->trial_days,
                'frequency_type' => 'days',
            ]);
        }

        if (blank($paymentMethod)) {
            unset($payload['card_token_id']);
            unset($payload['preapproval_plan_id']);
        } else {
            $payload['status'] = 'authorized';
        }

        if(filled($preapproval)) {
            Arr::forget($payload, 'auto_recurring');
        }

        return Http::mercadopago($application)
            ->post('/preapproval', $payload)
            ->json();
    }

    public function cancel(Application $application, string $id): void
    {
        Http::mercadopago($application)
            ->throw()
            ->put("/preapproval/{$id}", [
                'status' => SubscriptionStatus::CANCELLED,
            ]);
    }

    public function updatePreapproval(Application $application, string $id, int|float $amount): void
    {
        Http::mercadopago($application)
            ->throw()
            ->put("/preapproval/{$id}", [
                'auto_recurring' => [
                    'transaction_amount' => $amount,
                ],
            ]);
    }
}
