<?php

declare(strict_types=1);

namespace App\Actions\Customers;

use App\Concerns\Action;
use App\Enums\Environment;
use App\Models\Customer;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Throwable;

final class CreateCustomer extends Action
{
    /**
     * @throws LockTimeoutException
     * @throws Throwable
     */
    public function handle(string $name, string $email, Environment $environment): Customer
    {
        return $this->lock(function () use ($name, $email, $environment) {
            $customer = new Customer;
            $customer->name = $name;
            $customer->email = $email;
            $customer->environment = $environment;
            $customer->save();

            return $customer;
        }, ...func_get_args());
    }
}
