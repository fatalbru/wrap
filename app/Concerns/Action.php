<?php

declare(strict_types=1);

namespace App\Concerns;

use App\Dtos\AtomicOptions;
use BackedEnum;
use Closure;
use DateTimeInterface;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Throwable;
use UnitEnum;

/**
 * @template TReturn
 */
abstract class Action
{
    private const string EXECUTION_HANDLER = 'execute';

    protected bool $shouldLock = true;

    protected int $lockTtl = 10;

    protected int $lockWaitSeconds = 3;

    protected ?string $atomicKey = null;

    final public function atomicOptions(AtomicOptions $shouldLock): self
    {
        $this->lockTtl = $shouldLock->ttl;
        $this->lockWaitSeconds = $shouldLock->wait;
        $this->atomicKey = $shouldLock->key;

        return $this;
    }

    /**
     * @return TReturn
     *
     * @throws Throwable
     * @throws LockTimeoutException
     */
    final public function lock(Closure $closure, ...$args)
    {
        throw_if(! method_exists($this, self::EXECUTION_HANDLER), 'Execution handler not found.');

        if (! $this->shouldLock) {
            return $closure();
        }

        return cache()
            ->lock($this->generateAtomicLockId(...$args), $this->lockTtl)
            ->block($this->lockWaitSeconds, $closure);
    }

    protected function generateAtomicLockId(...$args): string
    {
        return $this->atomicKey ?: sprintf(
            '%s:%s',
            static::class,
            md5(serialize(array_map(fn ($a) => $this->simplifyArg($a), $args)))
        );
    }

    protected function simplifyArg(mixed $arg): mixed
    {
        if (is_object($arg)) {
            if (method_exists($arg, 'getKey')) {
                return [get_class($arg), $arg->getKey()];
            }

            if ($arg instanceof BackedEnum) {
                return [$arg::class, $arg->value];
            }

            if ($arg instanceof UnitEnum) {
                return [$arg::class, $arg->name];
            }

            if ($arg instanceof DateTimeInterface) {
                return $arg->format(DateTimeInterface::ATOM);
            }

            return spl_object_id($arg); // fallback
        }

        if (is_array($arg)) {
            return array_map(fn ($v) => $this->simplifyArg($v), $arg);
        }

        return $arg;
    }
}
