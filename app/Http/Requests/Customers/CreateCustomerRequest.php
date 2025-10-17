<?php

declare(strict_types=1);

namespace App\Http\Requests\Customers;

use App\Enums\Environment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => [
                'required',
                'email',
                Rule::unique('customers', 'email'),
            ],
            'name' => ['required', 'string'],
            'environment' => ['required', Rule::enum(Environment::class)],
        ];
    }
}
