<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\ProductType;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PriceResource extends JsonResource
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
            'name' => $this->name,
            'price' => $this->price,
            'frequency' => $this->when($this->product->type === ProductType::SUBSCRIPTION, fn () => $this->frequency),
            'trial_days' => $this->when($this->product->type === ProductType::SUBSCRIPTION, fn () => $this->trial_days),
            'product' => ProductResource::make($this->whenLoaded('product')),
            'environment' => $this->environment,
            'vendor_data' => [
                'id' => $this->vendor_id,
            ],
        ];
    }
}
