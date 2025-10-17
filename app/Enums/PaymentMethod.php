<?php

declare(strict_types=1);

namespace App\Enums;

enum PaymentMethod: string
{
    case CARD = 'card';
    case MERCADOPAGO = 'mercadopago';

    public function getLabel(): string
    {
        return match ($this) {
            self::CARD => 'Card',
            self::MERCADOPAGO => 'Mercado Pago',
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::CARD => 'credit-card',
            self::MERCADOPAGO => 'mercadopago',
        };
    }

    public static function parse(?string $value): ?PaymentMethod
    {
        return blank($value) ? null : ($value === 'account_money' ? self::MERCADOPAGO : self::CARD);
    }
}
