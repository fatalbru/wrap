<?php

declare(strict_types=1);

namespace App\Services\MercadoPago;

use App\Dtos\MercadoPago\Cards\TemporaryCardDto;
use App\Models\Application;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use SensitiveParameter;
use Throwable;

final class Payment
{
    public function get(Application $application, string $id): array
    {
        return Http::mercadopago($application)
            ->throw()
            ->get("/v1/payments/{$id}")
            ->json();
    }

    public function refund(Application $application, string $id, string $idempotency): array
    {
        return Http::mercadopago($application)
            ->withHeader('x-idempotency-key', $idempotency)
            ->throw()
            ->post("/v1/payments/{$id}/refunds")
            ->json();
    }

    /**
     * @throws Throwable
     */
    public function create(
        Application $application,
        #[SensitiveParameter] TemporaryCardDto $card,
        string $payerEmail,
        string $externalReference,
        string $description,
        int|float $amount,
        string $idempotency,
        int $installments = 1,
        array $metadata = [],
    ) {
        $payload = [
            'payer' => [
                'email' => $payerEmail,
            ],
            'installments' => $installments,
            'binary_mode' => true, // used to avoid "in process" payments
            'capture' => true,
            'external_reference' => $externalReference,
            'payment_method_id' => $card->paymentMethodId(),
            'description' => $description,
            'transaction_amount' => $amount,
            'token' => $card->token(),
            'statement_descriptor' => config('app.name'),
            'metadata' => $metadata,
        ];

        $validator = Validator::make($payload, [
            'payer.email' => ['required', 'email'],
            'installments' => ['required', 'numeric', 'min:1'],
            'binary_mode' => ['required', 'boolean'],
            'capture' => ['required', 'boolean'],
            'external_reference' => ['required', 'string', 'exists:orders,ksuid'],
            'payment_method_id' => ['required', 'string'],
            'description' => ['required', 'string', 'max:255'],
            'transaction_amount' => ['required', 'integer', 'min:0'],
            'token' => ['required', 'string'],
        ]);

        throw_if($validator->fails(), 'Malformed payment request');

        return Http::mercadopago($application)
            ->withHeader('x-idempotency-key', $idempotency)
            ->throw()
            ->post('/v1/payments', $payload)
            ->json();
    }
}
