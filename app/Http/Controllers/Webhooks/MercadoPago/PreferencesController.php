<?php

namespace App\Http\Controllers\Webhooks\MercadoPago;

use App\Actions\RegisterWebhookLog;
use App\Http\Controllers\Controller;
use App\Http\Requests\MercadoPago\PreferenceCallbackRequest;
use App\Services\MercadoPago\Preference as PreferenceService;
use Illuminate\Support\Facades\Log;

class PreferencesController extends Controller
{
    public function ipn(
        PreferenceCallbackRequest $request,
        PreferenceService $preferenceService,
        RegisterWebhookLog $registerWebhookLog
    ) {
        //        $registerWebhookLog->handle(
        //            $request->order(),
        //            $request->validated(),
        //            $request->idempotency(),
        //            paymentProvider: PaymentProvider::MERCADOPAGO
        //        );

        //        $preference = $preferenceService->get(
        //            $request->order()->application,
        //            $request->order()->vendor_id,
        //        );

        Log::debug(__CLASS__, $request->all());

        //        dd($preference);

        return response()->noContent();
    }
}
