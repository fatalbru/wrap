<?php

namespace App\Models;

use App\Enums\HandshakeType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Handshake extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['idempotency', 'payload', 'type', 'disposable'];

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
        return self::create([
            'type' => HandshakeType::JOB,
            'payload' => [
                'handler' => $jobHandler,
                'arguments' => $arguments,
            ],
            'idempotency' => $idempotency,
            'disposable' => $disposable,
        ]);
    }
}
