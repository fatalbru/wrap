<?php

declare(strict_types=1);

namespace App;

enum PaymentVendor: string
{
    case MERCADOPAGO = 'mercadopago';
    case MERCADOPAGO_CARD = 'mercadopago-card';

    public function getLabel(): string
    {
        return match ($this) {
            self::MERCADOPAGO => __('Mercado Pago'),
            self::MERCADOPAGO_CARD => __('Mercado Pago (Cards)'),
        };
    }
}
