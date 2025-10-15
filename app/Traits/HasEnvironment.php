<?php

declare(strict_types=1);

namespace App\Traits;

use App\Environment;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * @mixin Model
 */
trait HasEnvironment
{
    public function scopeEnvironment(Builder $builder, Environment $environment): void
    {
        $builder->where('environment', $environment);
    }

    public function scopeLive(Builder $builder): void
    {
        $builder->environment(Environment::LIVE);
    }

    public function scopeTest(Builder $builder): void
    {
        $builder->environment(Environment::TEST);
    }
}
