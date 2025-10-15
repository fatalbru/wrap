<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubscriptionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->ksuid,
            'status' => $this->status,
            'started_at' => $this->started_at,
            'canceled_at' => $this->canceled_at,
            'next_payment_at' => $this->next_payment_at,
            'trial_started_at' => $this->trial_started_at,
            'trial_ended_at' => $this->trial_ended_at,
            'trial' => $this->trial,
            'grace_period' => $this->grace_period,
            'customer' => CustomerResource::make($this->customer),
            'price' => PriceResource::make($this->price),
            'environment' => $this->environment,
            'vendor_data' => $this->vendor_data
        ];
    }
}
