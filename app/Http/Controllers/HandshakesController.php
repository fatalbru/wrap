<?php

namespace App\Http\Controllers;

use App\HandshakeType;
use App\Models\Handshake;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HandshakesController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, Handshake $handshake)
    {
        if ($handshake->type === HandshakeType::REROUTE) {
            $payload = $handshake->payload;
            $handshake->delete();
            return redirect()->route(
                data_get($payload, 'route'),
                data_get($payload, 'routeParams')
            );
        }

        abort(Response::HTTP_UNPROCESSABLE_ENTITY);
    }
}
