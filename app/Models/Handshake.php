<?php

namespace App\Models;

use App\HandshakeType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Handshake extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['idempotency', 'payload', 'type'];

    protected function casts()
    {
        return [
            'type' => HandshakeType::class,
            'payload' => 'json'
        ];
    }
}
