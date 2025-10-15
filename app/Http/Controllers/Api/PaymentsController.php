<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Actions\RefundPayment;
use App\Http\Controllers\Controller;
use App\Http\Resources\PaymentResource;
use App\Models\Payment;
use Illuminate\Http\Request;
use Throwable;

class PaymentsController extends Controller
{
    /**
     * @throws Throwable
     */
    function index(Request $request)
    {
        return Payment::search($request->get('search'))
            ->latest()
            ->paginate($request->integer('per_page', 50))
            ->toResourceCollection(PaymentResource::class);
    }

    function refund(Payment $payment, RefundPayment $refundPaymentAction)
    {
        $refundPaymentAction->handle($payment);

        return response()->noContent();
    }
}
