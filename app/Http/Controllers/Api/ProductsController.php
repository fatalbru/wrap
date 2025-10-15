<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\Request;
use Throwable;

class ProductsController extends Controller
{
    /**
     * @throws Throwable
     */
    public function index(Request $request)
    {
        return Product::with('prices')
            ->paginate($request->integer('per_page', 50))
            ->toResourceCollection(ProductResource::class);
    }
}
