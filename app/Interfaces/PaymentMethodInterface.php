<?php

namespace App\Interfaces;

interface PaymentMethodInterface
{
    public function lastFourDigits(): ?string;

    public function paymentTypeId(): ?string;

    public function paymentMethodId(): ?string;
}
