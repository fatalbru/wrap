<?php

namespace App\DTOs\MercadoPago\Preapprovals;

use WendellAdriel\ValidatedDTO\Casting\StringCast;
use WendellAdriel\ValidatedDTO\ValidatedDTO;

class PreapprovalLinkDto extends ValidatedDTO
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
