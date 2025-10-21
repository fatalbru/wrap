<?php

namespace App\DTOs\MercadoPago\Preferences;

use WendellAdriel\ValidatedDTO\Casting\StringCast;
use WendellAdriel\ValidatedDTO\ValidatedDTO;

class PreferenceLinkDto extends ValidatedDTO
{
    public string $init_point;

    protected function rules(): array
    {
        return [
            'init_point' => ['required', 'url'],
        ];
    }

    protected function casts(): array
    {
        return [
            'init_point' => new StringCast(),
        ];
    }

    protected function defaults(): array
    {
        return [];
    }
}
