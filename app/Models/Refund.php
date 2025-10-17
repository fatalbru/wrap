<?php

declare(strict_types=1);

namespace App\Models;

use App\Observers\RefundObserver;
use App\Traits\HasKsuid;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[ObservedBy(RefundObserver::class)]
class Refund extends Model
{
    use HasFactory, HasKsuid;

    protected $fillable = ['amount'];
}
