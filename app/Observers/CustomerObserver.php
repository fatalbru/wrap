<?php

namespace App\Observers;

use App\Events\Customers\CustomerCreated;
use App\Events\Customers\CustomerDeleted;
use App\Events\Customers\CustomerUpdated;
use App\Models\Customer;

class CustomerObserver
{
    public function created(Customer $customer): void
    {
        event(new CustomerCreated($customer));
    }

    public function updated(Customer $customer): void
    {
        event(new CustomerUpdated($customer));
    }

    public function deleted(Customer $customer): void
    {
        event(new CustomerDeleted($customer));
    }
}
