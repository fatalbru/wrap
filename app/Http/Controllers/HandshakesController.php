<?php

namespace App\Http\Controllers;

use App\Enums\HandshakeType;
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
        $payload = $handshake->payload;

        if ($handshake->disposable) {
            $handshake->delete();
        }

        if ($handshake->type === HandshakeType::REROUTE) {
            return redirect()->route(
                data_get($payload, 'route'),
                data_get($payload, 'routeParams')
            );
        }

        if ($handshake->type === HandshakeType::JOB) {
            $handler = data_get($payload, 'handler');

            dispatch(new $handler([
                ...$request->all(),
                ...data_get($payload, 'arguments'),
            ]));

            return response()->noContent(Response::HTTP_OK);
        }

        abort(Response::HTTP_UNPROCESSABLE_ENTITY);
    }
}
