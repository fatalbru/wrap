<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Order;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CheckoutResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->ksuid,
            'environment' => $this->environment,
            'redirect_url' => $this->redirect_url,
            'completed_at' => $this->completed_at,
            'canceled_at' => $this->canceled_at,
            'expires_at' => $this->expires_at,
            'url' => url(route('checkout', $this)),
            'min_installments' => $this->when(
                $this->checkoutable instanceof Order,
                fn() => $this->min_installments
            ),
            'max_installments' => $this->when(
                $this->checkoutable instanceof Order,
                fn() => $this->max_installments
            ),
            'customer' => CustomerResource::make($this->customer),
            'subscription' => $this->when(
                $this->checkoutable instanceof Subscription,
                fn() => SubscriptionResource::make($this->checkoutable)
            ),
            'order' => $this->when(
                $this->checkoutable instanceof Order,
                fn() => OrderResource::make($this->checkoutable)
            ),
        ];
    }
}
