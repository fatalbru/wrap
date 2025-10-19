<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MercadoPagoController extends Controller
{
    function __invoke(Request $request, ?string $mode = null)
    {
        Log::debug(__CLASS__, $request->all());
    }
}
