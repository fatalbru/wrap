<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->ksuid,
            'email' => $this->email,
            'name' => $this->name,
            'environment' => $this->environment,
        ];
    }
}
