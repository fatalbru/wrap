<?php

declare(strict_types=1);

namespace App\Actions\Checkouts;

use App\Concerns\Action;
use App\Enums\Environment;
use App\Enums\OrderStatus;
use App\Enums\PaymentVendor;
use App\Enums\ProductType;
use App\Enums\SubscriptionStatus;
use App\Models\Checkout;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Price;
use App\Models\Subscription;
use Carbon\Carbon;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Throwable;

final class CreateCheckout extends Action
{
    /**
     * @throws LockTimeoutException|Throwable
     */
    public function execute(
        Customer    $customer,
        Price       $price,
        Environment $environment,
        ?Carbon     $expiresAt = null
    ): Checkout
    {
        throw_if($customer->environment !== $environment, 'Customer environment does not match.');
        throw_if($price->environment !== $environment, 'Price environment does not match.');

        return $this->lock(function () use ($customer, $price, $environment, $expiresAt) {
            $checkout = new Checkout;
            $checkout->customer()->associate($customer);

            if ($price->product->type === ProductType::SUBSCRIPTION) {
                $subscription = new Subscription();
                $subscription->customer()->associate($customer);
                $subscription->price()->associate($price);
                $subscription->status = SubscriptionStatus::PENDING;
                $subscription->vendor = PaymentVendor::MERCADOPAGO;
                $subscription->environment = $environment;
                $subscription->save();

                $checkout->checkoutable()->associate($subscription);
            } else {
                $order = new Order();
                $order->customer()->associate($customer);
                $order->status = OrderStatus::PENDING;
                $order->environment = $environment;
                $order->save();

                $orderItem = new OrderItem();
                $orderItem->order()->associate($order);
                $orderItem->price()->associate($price);
                $orderItem->save();

                $checkout->checkoutable()->associate($order);
            }

            $checkout->environment = $environment;
            $checkout->expires_at = $expiresAt;
            $checkout->save();

            return $checkout;
        }, ...func_get_args());
    }
}
