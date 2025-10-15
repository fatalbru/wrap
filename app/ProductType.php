<?php

declare(strict_types=1);

namespace App;

use Illuminate\Support\Str;

enum ProductType: string
{
    case ORDER = 'order';
    case SUBSCRIPTION = 'subscription';

    public function plural(): string
    {
        return Str::plural($this->value);
    }
}
