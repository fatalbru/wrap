<?php

namespace App\Events\Customers;

use App\Interfaces\OutgoingWebhookInterface;
use App\Models\Customer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CustomerDeleted implements OutgoingWebhookInterface
{
    use Dispatchable, SerializesModels;

    public function __construct(public readonly Customer $customer) {}

    public function getWebhookData(): array
    {
        return [];
    }

    public function getModel(): Model
    {
        return $this->customer;
    }
}
