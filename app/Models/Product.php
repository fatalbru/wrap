<?php

declare(strict_types=1);

namespace App\Models;

use App\Environment;
use App\ProductType;
use App\Traits\HasKsuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory, HasKsuid;

    protected $fillable = ['type', 'name', 'environment'];

    protected function casts()
    {
        return [
            'type' => ProductType::class,
            'environment' => Environment::class,
        ];
    }

    public function prices(): HasMany
    {
        return $this->hasMany(Price::class);
    }
}
