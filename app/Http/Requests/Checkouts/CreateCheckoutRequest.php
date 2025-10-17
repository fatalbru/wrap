<?php

declare(strict_types=1);

namespace App\Http\Requests\Checkouts;

use App\Enums\Environment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateCheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'environment' => ['required', Rule::enum(Environment::class)],
            'price_id' => ['required', Rule::exists('prices', 'ksuid')],
            'customer_id' => ['required', Rule::exists('customers', 'ksuid')],
        ];
    }
}
