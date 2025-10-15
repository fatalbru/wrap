<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Application;
use App\Models\Checkout;
use App\Models\OrderItem;
use App\PaymentVendor;
use App\ProductType;
use App\Services\MercadoPago\Preference as PreferenceService;

final readonly class CreatePreferenceLink
{
    public function __construct(private PreferenceService $preferenceService) {}

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

        $preferenceData = $this->preferenceService->create(
            $application,
            $order->items->map(fn (OrderItem $orderItem) => [
                'id' => $orderItem->price->ksuid,
                'title' => $orderItem->price->name,
                'quantity' => $orderItem->quantity,
                'unit_price' => $orderItem->price->price,
            ])->toArray(),
            $checkout->checkoutable->ksuid,
            url(route('checkout.callback', $checkout)),
            url(route('api.orders.ipn', $order)),
        );

        $order->update([
            'vendor_id' => data_get($preferenceData, 'id'),
            'vendor_data' => $preferenceData,
        ]);

        return $preferenceData;
    }
}
