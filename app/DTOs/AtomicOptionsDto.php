<?php

namespace App\DTOs;

use WendellAdriel\ValidatedDTO\Casting\IntegerCast;
use WendellAdriel\ValidatedDTO\Casting\StringCast;
use WendellAdriel\ValidatedDTO\ValidatedDTO;

class AtomicOptionsDto extends ValidatedDTO
{
    public ?int $ttl = null;
    public ?int $wait = null;
    public ?string $key = null;

    protected function rules(): array
    {
        return [
            'ttl' => ['nullable', 'integer'],
            'wait' => ['nullable', 'integer'],
            'key' => ['nullable', 'string'],
        ];
    }

    protected function defaults(): array
    {
        return [];
    }

    protected function casts(): array
    {
        return [
            'ttl' => new IntegerCast(),
            'wait' => new IntegerCast(),
            'key' => new StringCast(),
        ];
    }
}
