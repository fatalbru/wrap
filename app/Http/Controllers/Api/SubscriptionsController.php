<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Actions\CancelSubscription;
use App\Actions\ListPayments;
use App\Http\Controllers\Controller;
use App\Http\Requests\Subscriptions\UpdateSubscriptionPrice;
use App\Http\Resources\PaymentResource;
use App\Http\Resources\SubscriptionResource;
use App\Models\Price;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Throwable;
use App\Services\MercadoPago\Subscription as SubscriptionService;
use App\Actions\UpdateSubscriptionPrice as UpdateSubscriptionPriceAction;

class SubscriptionsController extends Controller
{
    /**
     * @throws Throwable
     */
    public function index(Request $request)
    {
        return Subscription::with(['price', 'customer'])
            ->paginate($request->integer('per_page', 50))
            ->toResourceCollection(SubscriptionResource::class);
    }

    public function show(Subscription $subscription)
    {
        return $subscription->toResource(SubscriptionResource::class);
    }

    public function raw(Subscription $subscription, SubscriptionService $subscriptionService)
    {
        return response()->json($subscriptionService->get($subscription->application, $subscription->vendor_id));
    }

    /**
     * @throws Throwable
     */
    public function payments(Subscription $subscription, ListPayments $listPayments)
    {
        return $listPayments->handle($subscription)->toResourceCollection(PaymentResource::class);
    }

    public function cancel(Subscription $subscription, CancelSubscription $cancelSubscription)
    {
        $cancelSubscription->handle($subscription);

        return response()->noContent();
    }

    /**
     * @throws Throwable
     */
    public function updatePlan(
        UpdateSubscriptionPrice       $request,
        Subscription                  $subscription,
        UpdateSubscriptionPriceAction $updateSubscriptionPriceAction
    )
    {
        $updateSubscriptionPriceAction->handle($subscription, Price::findByKsuid($request->get('price_id')));

        return response()->noContent();
    }
}
