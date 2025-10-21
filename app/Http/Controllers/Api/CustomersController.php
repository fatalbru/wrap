<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Actions\Customers\CreateCustomer;
use App\Enums\Environment;
use App\Http\Controllers\Controller;
use App\Http\Requests\Customers\CreateCustomerRequest;
use App\Http\Requests\Customers\UpdateCustomerRequest;
use App\Http\Resources\CustomerResource;
use App\Models\Customer;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Throwable;

class CustomersController extends Controller
{
    use ValidatesRequests;

    /**
     * @throws Throwable
     */
    public function index(Request $request)
    {
        return Customer::search($request->get('search'))
            ->paginate($request->integer('per_page', 50))
            ->toResourceCollection(CustomerResource::class);
    }

    /**
     * @throws LockTimeoutException
     */
    public function store(CreateCustomerRequest $request, CreateCustomer $createCustomer)
    {
        return $createCustomer->handle(
            $request->get('name'),
            $request->get('email'),
            $request->enum('environment', Environment::class)
        )->toResource(CustomerResource::class);
    }

    public function show(Customer $customer)
    {
        return CustomerResource::make($customer);
    }

    public function portalUrl(Request $request, Customer $customer)
    {
        $this->validate($request, [
            'back_url' => ['required', 'url'],
        ]);

        $portalIdentifier = base64_encode(encrypt([
            'customer_id' => $customer->id,
            'expires_at' => now()->addMinutes(10),
            'back_url' => $request->get('back_url'),
        ]));

        return route('customer_portal.home', compact('portalIdentifier'));
    }

    public function update(UpdateCustomerRequest $request, Customer $customer)
    {
        return tap($customer, function (Customer $customer) use ($request): void {
            $customer->update($request->validated());
        })->toResource(CustomerResource::class);
    }
}
