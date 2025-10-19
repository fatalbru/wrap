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
    public function execute(string $name, string $email, Environment $environment): Customer
    {
        return $this->lock(
            fn () => Customer::create(compact('name', 'email', 'environment')),
            ...func_get_args()
        );
    }
}
