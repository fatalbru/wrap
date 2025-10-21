<?php

namespace App\Models;

use App\Enums\HandshakeType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

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
        array  $arguments,
        string $idempotency,
        bool   $disposable = true,
    ): Handshake
    {
        $handshake = new Handshake();
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
        $handshake = new Handshake();
        $handshake->type = HandshakeType::REROUTE;
        $handshake->idempotency = $idempotency;
        $handshake->payload = $payload;
        $handshake->save();

        return $handshake;
    }
}
