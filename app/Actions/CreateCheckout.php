<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\Environment;
use App\Enums\OrderStatus;
use App\Enums\PaymentVendor;
use App\Enums\ProductType;
use App\Enums\SubscriptionStatus;
use App\Models\Checkout;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Price;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Throwable;

final class CreateCheckout
{
    /**
     * @throws LockTimeoutException|Throwable
     */
    public function handle(Customer $customer, Price $price, Environment $environment): Checkout
    {
        throw_if($customer->environment !== $environment, 'Customer environment does not match.');
        throw_if($price->environment !== $environment, 'Price environment does not match.');

        return cache()
            ->lock(__CLASS__.$customer->id.$price->id, 10)
            ->block(3, function () use ($customer, $price, $environment) {
                if ($price->product->type === ProductType::SUBSCRIPTION) {
                    $checkoutable = $customer->subscriptions()->create([
                        'price_id' => $price->id,
                        'status' => SubscriptionStatus::PENDING,
                        'vendor' => PaymentVendor::MERCADOPAGO,
                        'environment' => $environment,
                    ]);
                } else {
                    $checkoutable = tap($customer->orders()->create([
                        'status' => OrderStatus::PENDING,
                        'environment' => $environment,
                    ]), function (Order $order) use ($price): void {
                        $order->items()->create([
                            'price_id' => $price->id,
                        ]);
                    });
                }

                $checkout = new Checkout;
                $checkout->checkoutable()->associate($checkoutable);
                $checkout->customer()->associate($customer);
                $checkout->environment = $environment;
                $checkout->save();

                return $checkout;
            });
    }
}
