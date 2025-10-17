<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Actions\CreateCheckout;
use App\Enums\Environment;
use App\Events\MercadoPago\WebhookReceived;
use App\Http\Controllers\Controller;
use App\Http\Requests\Checkouts\CreateCheckoutRequest;
use App\Http\Resources\CheckoutResource;
use App\Models\Checkout;
use App\Models\Customer;
use App\Models\Price;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class CheckoutsController extends Controller
{
    /**
     * @throws Throwable
     * @throws LockTimeoutException
     */
    public function store(CreateCheckoutRequest $request, CreateCheckout $createCheckout)
    {
        return $createCheckout->handle(
            Customer::findByKsuid($request->get('customer_id')),
            Price::findByKsuid($request->get('price_id')),
            $request->enum('environment', Environment::class)
        )->toResource(CheckoutResource::class);
    }

    public function show(Checkout $checkout)
    {
        return $checkout->toResource(CheckoutResource::class);
    }

    public function ipn(Request $request)
    {
        Log::debug(__CLASS__, $request->toArray());
        event(new WebhookReceived($request->toArray()));

        return response()->noContent();
    }
}
