<?php

declare(strict_types=1);

namespace App\Actions;

use App\Environment;
use App\Models\Customer;

final class CreateCustomer
{
    public function handle(string $name, string $email, Environment $environment): Customer
    {
        return Customer::create(compact('name', 'email', 'environment'));
    }
}
