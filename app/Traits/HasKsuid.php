<?php

declare(strict_types=1);

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * @mixin Model
 *
 * @phpstan-template TModel of Model
 *
 * @phpstan-consistent-constructor
 */
trait HasKsuid
{
    /**
     * @phpstan-param Builder<static> $query
     *
     * @psalm-param  Builder<static> $query
     *
     * @phpstan-return Builder<static>
     *
     * @psalm-return Builder<static>
     */
    public function scopeByKsuid(Builder $query, string $ksuid): Builder
    {
        return $query->where('ksuid', $ksuid);
    }

    public static function findByKsuid(string $ksuid): static
    {
        return static::query()
            ->where('ksuid', $ksuid)
            ->firstOrFail();
    }

    public static function generateKsuid(string $prefix, int $length = 8): string
    {
        do {
            $timestamp = base_convert((string) now()->timestamp, 10, 36); // shorter, time-based component
            $random = Str::random($length);
            $ksuid = "{$prefix}_{$timestamp}{$random}";
        } while (self::where('ksuid', $ksuid)->exists());

        return $ksuid;
    }
}
