<?php

declare(strict_types=1);

namespace App\Interfaces;

interface ExternalPaymentHandlerInterface
{
    public function redirectLink(): ?string;
}
