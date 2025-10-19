<?php

declare(strict_types=1);

namespace App\Actions\Providers\MercadoPago;

use App\Actions\Applications\AssignApplication;
use App\Concerns\Action;
use App\Dtos\MercadoPago\Preferences\PreferenceLink;
use App\Enums\PaymentVendor;
use App\Enums\ProductType;
use App\Jobs\MercadoPago\Preferences\HandlePreferenceCallback;
use App\Models\Checkout;
use App\Models\Handshake;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\MercadoPago\Preference as PreferenceService;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Throwable;

final class CreatePreferenceLink extends Action
{
    public function __construct(
        private readonly PreferenceService $preferenceService,
        private readonly AssignApplication $assignApplication,
    ) {}

    /**
     * @throws LockTimeoutException
     * @throws Throwable
     */
    public function execute(Checkout $checkout): PreferenceLink
    {
        return $this->lock(function () use ($checkout) {
            /** @var Order $order */
            $order = $checkout->checkoutable;

            if (blank(data_get($order->vendor_data, 'init_point'))) {
                $order->application()->associate(
                    $this->assignApplication->execute(
                        $order->environment,
                        PaymentVendor::MERCADOPAGO,
                        ProductType::ORDER
                    )
                );

                $order->save();

                $handshake = Handshake::forJob(
                    HandlePreferenceCallback::class,
                    [
                        'checkout_id' => $checkout->id,
                        'order_id' => $order->id,
                    ],
                    md5($order->ksuid.uniqid().time()),
                    disposable: false,
                );

                $response = $this->preferenceService->create(
                    $order->application,
                    $order->items->map(fn (OrderItem $orderItem) => [
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
                    'vendor_id' => data_get($response, 'id'),
                    'vendor_data' => $response,
                ]);
            }

            return PreferenceLink::make($order->vendor_data);
        }, ...func_get_args());
    }
}
