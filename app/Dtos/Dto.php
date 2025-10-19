<?php

declare(strict_types=1);

namespace App\Dtos;

class Dto
{
    public static function make(...$args): Dto
    {
        return new self(...$args);
    }
}
