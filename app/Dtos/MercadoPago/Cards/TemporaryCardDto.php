<?php

declare(strict_types=1);

namespace App\Dtos\MercadoPago\Cards;

use App\Dtos\Dto;
use App\Interfaces\PaymentMethodInterface;

final class TemporaryCardDto extends Dto implements PaymentMethodInterface
{
    public function __construct(private readonly array $card) {}

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
