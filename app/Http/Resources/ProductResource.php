<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
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
            'type' => $this->type,
            'prices' => PriceResource::collection($this->whenLoaded('prices')),
            'environment' => $this->environment,
        ];
    }
}
