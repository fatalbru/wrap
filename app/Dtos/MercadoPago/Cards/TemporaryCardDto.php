<?php

declare(strict_types=1);

namespace App\Dtos\MercadoPago\Cards;

final readonly class TemporaryCardDto
{
    public function __construct(private array $card)
    {
    }

    public static function make(array $card): TemporaryCardDto
    {
        return new self($card);
    }

    public function token(): string
    {
        return data_get($this->card, 'token');
    }

    public function lastFourDigits(): ?string
    {
        return data_get($this->card, 'lastFourDigits');
    }

    public function paymentTypeId(): ?string
    {
        return data_get($this->card, 'paymentTypeId');
    }

    public function paymentMethodId(): ?string
    {
        return data_get($this->card, 'payment_method_id');
    }
}
