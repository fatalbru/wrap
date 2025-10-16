<?php

declare(strict_types=1);

namespace App;

enum PaymentProvider: string
{
    case MERCADOPAGO = 'mercadopago';
}
