<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
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
            'completed_at' => $this->completed_at,
            'canceled_at' => $this->canceled_at,
            'items' => OrderItemResource::collection($this->items),
            'payments' => PaymentResource::collection($this->payments),
            'vendor_data' => $this->vendor_data,
        ];
    }
}
