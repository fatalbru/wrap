<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

class UseCents implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        return round($value / 100, 2);
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        return (int) $value * 100;
    }
}
