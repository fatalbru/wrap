<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PriceResource;
use App\Models\Price;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class PricesController extends Controller
{
    public function index(Request $request, ?Product $product = null)
    {
        return Price::with(['product'])
            ->when(filled($product), fn(Builder $builder) => $builder->whereBelongsTo($product))
            ->paginate($request->integer('per_page', 50))
            ->toResourceCollection(PriceResource::class);
    }
}
