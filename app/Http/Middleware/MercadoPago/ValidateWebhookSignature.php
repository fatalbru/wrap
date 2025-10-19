<?php

namespace App\Http\Middleware\MercadoPago;

use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class ValidateWebhookSignature
{
    const string SIGNATURE_HEADER = 'x-signature';

    const string REQUEST_ID_HEADER = 'x-request-id';

    const string DATA_ID_PATH = 'data.id';

    public function handle(Request $request, Closure $next): Response
    {
        if (app()->isLocal()) {
            Log::debug(__CLASS__, [
                'data' => $request->all(),
                'headers' => $request->headers->all(),
            ]);
        }

        $webhookSignature = config('mrr.webhook_signature');

        abort_if(blank($webhookSignature), Response::HTTP_FORBIDDEN, 'Webhook Signature conflict');

        $xSignature = $request->header(self::SIGNATURE_HEADER);
        $xRequestId = $request->header(self::REQUEST_ID_HEADER);
        $dataId = data_get($request->all(), self::DATA_ID_PATH);

        abort_if(
            blank($xRequestId) || blank($xSignature) || blank($dataId),
            Response::HTTP_FORBIDDEN,
            'Webhook params missing'
        );

        $ts = null;
        $hash = null;

        foreach (explode(',', $xSignature) as $part) {
            [$k, $v] = array_pad(explode('=', trim($part), 2), 2, null);

            if ($k === 'ts') {
                $ts = $v;
            } elseif ($k === 'v1') {
                $hash = $v;
            }
        }

        abort_if(blank($ts) || blank($hash), Response::HTTP_FORBIDDEN, 'Malformed signature');

        $requestTimestamp = Carbon::createFromTimestamp($ts);

        abort_if(
            $requestTimestamp->addSeconds(config('mrr.webhook_tolerance'))->isPast(),
            Response::HTTP_FORBIDDEN,
            'Stale/invalid timestamp'
        );

        $manifest = "id:{$dataId};request-id:{$xRequestId};ts:{$ts};";

        abort_if(
            ! hash_equals(hash_hmac('sha256', $manifest, $webhookSignature), $hash),
            Response::HTTP_FORBIDDEN,
            'Invalid signature'
        );

        return $next($request);
    }
}
