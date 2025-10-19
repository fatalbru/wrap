<?php

declare(strict_types=1);

namespace App\Dtos\MercadoPago\Preferences;

use App\Dtos\Dto;
use App\Interfaces\ExternalPaymentHandlerInterface;

final class PreferenceLink extends Dto implements ExternalPaymentHandlerInterface
{
    public function __construct(protected readonly array $data) {}

    public function redirectLink(): string
    {
        return data_get($this->data, 'init_point');
    }
}
