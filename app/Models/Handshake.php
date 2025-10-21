<?php

namespace App\Models;

use App\Enums\HandshakeType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property HandshakeType $type
 * @property string $idempotency
 * @property array<array-key, mixed> $payload
 * @property bool $disposable
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 * @property \Carbon\CarbonImmutable|null $deleted_at
 * @method static \Database\Factories\HandshakeFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Handshake newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Handshake newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Handshake onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Handshake query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Handshake whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Handshake whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Handshake whereDisposable($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Handshake whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Handshake whereIdempotency($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Handshake wherePayload($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Handshake whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Handshake whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Handshake withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Handshake withoutTrashed()
 * @mixin \Eloquent
 */
class Handshake extends Model
{
    use HasFactory, SoftDeletes;

    protected function casts()
    {
        return [
            'type' => HandshakeType::class,
            'payload' => 'json',
            'disposable' => 'bool',
        ];
    }

    public static function forJob(
        string $jobHandler,
        array $arguments,
        string $idempotency,
        bool $disposable = true,
    ): Handshake {
        $handshake = new Handshake;
        $handshake->type = HandshakeType::JOB;
        $handshake->idempotency = $idempotency;
        $handshake->disposable = $disposable;
        $handshake->payload = [
            'handler' => $jobHandler,
            'arguments' => $arguments,
        ];
        $handshake->save();

        return $handshake;
    }

    public static function shouldReroute(string $idempotency, array $payload): Handshake
    {
        $handshake = new Handshake;
        $handshake->type = HandshakeType::REROUTE;
        $handshake->idempotency = $idempotency;
        $handshake->payload = $payload;
        $handshake->save();

        return $handshake;
    }
}
