<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Events\MercadoPago\WebhookReceived;
use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class OrdersController extends Controller
{
    /**
     * @throws Throwable
     */
    public function index(Request $request)
    {
        return Order::with(['items', 'payments', 'customer'])
            ->when(str_starts_with($request->get('search', ''), 'ord_'), function ($query) use ($request) {
                $query->where('ksuid', $request->get('search'));
            })
            ->when(str_starts_with($request->get('search', ''), 'price_'), function ($query) use ($request) {
                $query->whereHas('items', function ($query) use ($request) {
                    $query->whereHas('price', function ($query) use ($request) {
                        $query->where('ksuid', $request->get('search'));
                    });
                });
            })
            ->when(str_starts_with($request->get('search', ''), 'cus_'), function ($query) use ($request) {
                $query->whereHas('customer', function ($query) use ($request) {
                    $query->where('ksuid', $request->get('search'));
                });
            })
            ->paginate($request->integer('per_page', 50))
            ->toResourceCollection(OrderResource::class);
    }

    public function show(Order $order)
    {
        return $order->toResource(OrderResource::class);
    }

    public function ipn(Request $request, Order $order)
    {
        Log::debug(__CLASS__, $request->toArray());

        event(new WebhookReceived(
            $request->toArray(),
            $order
        ));

        return response()->noContent();
    }
}
