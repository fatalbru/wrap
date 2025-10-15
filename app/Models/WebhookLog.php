<?php

namespace App\Models;

use App\PaymentVendor;
use Illuminate\Database\Eloquent\Model;

class WebhookLog extends Model
{
    protected $fillable = ['application_id', 'payload', 'vendor'];

    protected function casts()
    {
        return [
            'vendor' => PaymentVendor::class,
            'payload' => 'json'
        ];
    }
}
