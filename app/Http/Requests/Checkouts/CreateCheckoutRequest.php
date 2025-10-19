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
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'environment' => ['required', 'bail', Rule::enum(Environment::class)],
            'price_id' => [
                'required', Rule::exists('prices', 'ksuid')
                    ->where('environment', $this->enum('environment', Environment::class))
            ],
            'customer_id' => [
                'required', Rule::exists('customers', 'ksuid')
                    ->where('environment', $this->enum('environment', Environment::class))
            ],
            'expires_at' => ['nullable', 'date', 'after_or_equal:now'],
        ];
    }
}
