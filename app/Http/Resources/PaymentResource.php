<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->ksuid,
            'status' => $this->status,
            'decline_reason' => $this->decline_reason,
            'paid_at' => $this->paid_at,
            'refunded_at' => $this->refunded_at,
            'environment' => $this->environment,
            'vendor_data' => $this->vendor_data,
        ];
    }
}
