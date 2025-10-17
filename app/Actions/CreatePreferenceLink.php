<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\PaymentVendor;
use App\Enums\ProductType;
use App\Jobs\MercadoPago\Preferences\HandlePreferenceCallback;
use App\Models\Application;
use App\Models\Checkout;
use App\Models\Handshake;
use App\Models\OrderItem;
use App\Services\MercadoPago\Preference as PreferenceService;

final readonly class CreatePreferenceLink
{
    public function __construct(private PreferenceService $preferenceService)
    {
    }

    public function handle(Checkout $checkout): array
    {
        $order = $checkout->checkoutable;

        if (filled(data_get($order->vendor_data, 'init_point'))) {
            return $order->vendor_data;
        }

        $application = Application::assign(
            PaymentVendor::MERCADOPAGO,
            $order->environment,
            ProductType::ORDER
        );

        $order->application()->associate($application);
        $order->save();

        $handshake = Handshake::forJob(
            HandlePreferenceCallback::class,
            [
                'checkout_id' => $checkout->id,
                'order_id' => $order->id,
            ],
            md5($order->ksuid . uniqid() . time()),
            disposable: false,
        );

        $preference = $this->preferenceService->create(
            $application,
            $order->items->map(fn(OrderItem $orderItem) => [
                'id' => $orderItem->price->ksuid,
                'title' => $orderItem->price->name,
                'quantity' => $orderItem->quantity,
                'unit_price' => $orderItem->price->price,
            ])->toArray(),
            $order->ksuid,
            url(route('checkout.callback', $checkout)),
            url(route('handshake', $handshake)),
        );

        $order->update([
            'vendor_id' => data_get($preference, 'id'),
            'vendor_data' => $preference,
        ]);

        return $preference;
    }
}
