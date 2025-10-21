<?php

namespace App\DTOs;

use App\Enums\PaymentMethod;
use Illuminate\Validation\Rule;
use WendellAdriel\ValidatedDTO\Attributes\Map;
use WendellAdriel\ValidatedDTO\Casting\EnumCast;
use WendellAdriel\ValidatedDTO\Casting\StringCast;
use WendellAdriel\ValidatedDTO\ValidatedDTO;

/**
 * @property-read  string|null $paymentMethodId
 */
class PaymentMethodDto extends ValidatedDTO
{
    public PaymentMethod $paymentMethod;
    public ?string $token;
    public ?string $lastFourDigits;
    public ?string $paymentTypeId;

    #[Map(data: 'paymentMethodId')]
    public ?string $payment_method_id;

    protected function rules(): array
    {
        return [
            'paymentMethod' => ['required', Rule::enum(PaymentMethod::class)],
            'token' => ['required_if:paymentMethod,card', 'string'],
            'lastFourDigits' => ['nullable', 'string'],
            'paymentTypeId' => ['nullable', 'string'],
            'payment_method_id' => ['nullable', 'string'],
        ];
    }

    protected function defaults(): array
    {
        return [
            'paymentMethod' => PaymentMethod::CARD,
            'lastFourDigits' => null,
            'paymentTypeId' => null,
            'payment_method_id' => null,
        ];
    }

    protected function casts(): array
    {
        return [
            'paymentMethod' => new EnumCast(PaymentMethod::class),
            'token' => new StringCast(),
            'lastFourDigits' => new StringCast(),
            'paymentTypeId' => new StringCast(),
            'payment_method_id' => new StringCast(),
        ];
    }
}
