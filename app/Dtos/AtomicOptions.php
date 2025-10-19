<?php

declare(strict_types=1);

namespace App\Dtos;

final class AtomicOptions extends Dto
{
    public function __construct(
        public ?int $ttl = null,
        public ?int $wait = null,
        public ?string $key = null,
    ) {}
}
