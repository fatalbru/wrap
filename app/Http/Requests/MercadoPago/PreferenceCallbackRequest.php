<?php

namespace App\Http\Requests\MercadoPago;

use App\Models\Checkout;
use App\Models\Order;
use Illuminate\Foundation\Http\FormRequest;

class PreferenceCallbackRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function prepareForValidation(): void
    {
        $this->merge(decrypt($this->route('signature')));
    }

    public function rules(): array
    {
        return [
            'idempotency' => ['required', 'unique:webhook_logs,idempotency'],
            'order_id' => ['required', 'exists:orders,id'],
            'checkout_id' => ['required', 'exists:checkouts,id'],
        ];
    }

    public function order()
    {
        return Order::find($this->integer('order_id'));
    }

    public function checkout()
    {
        return Checkout::orders()->findOrFail($this->integer('checkout_id'));
    }

    public function idempotency(): string
    {
        return $this->input('idempotency');
    }
}
