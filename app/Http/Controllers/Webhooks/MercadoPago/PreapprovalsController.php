<?php

namespace App\Http\Controllers\Webhooks\MercadoPago;

use App\Actions\Payments\CreatePayment;
use App\Actions\Webhooks\RegisterWebhookLog;
use App\DTOs\PaymentMethodDto;
use App\Enums\PaymentMethod;
use App\Enums\PaymentProvider;
use App\Enums\PaymentStatus;
use App\Enums\PaymentVendor;
use App\Enums\SubscriptionStatus;
use App\Exceptions\IdempotencyOverlap;
use App\Http\Controllers\Controller;
use App\Models\Checkout;
use App\Models\Subscription;
use App\Services\MercadoPago\Subscription as SubscriptionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class PreapprovalsController extends Controller
{
    /**
     * Malformed/abusive requests have no mercy here
     *
     * @return RedirectResponse
     *
     * @throws IdempotencyOverlap
     */
    public function preapprovalCallback(
        string              $signature,
        SubscriptionService $subscriptionService,
        RegisterWebhookLog  $registerWebhookLog,
        CreatePayment       $createPayment,
    )
    {
        $signature = decrypt($signature);

        $validator = Validator::make($signature, [
            'checkout_id' => ['required', 'exists:checkouts,id'],
            'subscription_id' => ['required', 'exists:subscriptions,id'],
            'idempotency' => ['required', 'unique:webhook_logs,idempotency'],
        ]);

        if ($validator->fails()) {
            abort(Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        Log::debug(__CLASS__, Arr::wrap($signature));

        /** @var ?Checkout $checkout */
        $checkout = Checkout::find(data_get($signature, 'checkout_id'));

        /** @var ?Subscription $subscription */
        $subscription = Subscription::find(data_get($signature, 'subscription_id'));

        if (!$checkout->checkoutable instanceof Subscription || $checkout->checkoutable->id !== $subscription->id) {
            abort(Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $registerWebhookLog->execute(
            $subscription,
            $signature,
            data_get($signature, 'idempotency'),
            paymentProvider: PaymentProvider::MERCADOPAGO
        );

        $preapproval = $subscriptionService->get($subscription->application, $subscription->vendor_id);

        Log::debug(__CLASS__, Arr::wrap($preapproval));

        $status = SubscriptionStatus::tryFrom(data_get($preapproval, 'status'));

        if (blank($status)) {
            abort(Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $registerWebhookLog->execute($subscription, $preapproval, paymentProvider: PaymentProvider::MERCADOPAGO);

        if ($status === SubscriptionStatus::AUTHORIZED && blank($subscription->started_at)) {
            $checkout->complete();

            $subscription->status = SubscriptionStatus::AUTHORIZED;
            $subscription->started_at = now();
            $subscription->save();

            $paymentMethod = new PaymentMethodDto([
                'paymentMethod' => PaymentMethod::MERCADOPAGO,
                ... $preapproval
            ]);

            $createPayment->execute(
                $subscription,
                $subscription->price->has_trial ? 0 : $subscription->price->price,
                PaymentStatus::APPROVED,
                $paymentMethod->paymentMethod === PaymentMethod::MERCADOPAGO ? PaymentVendor::MERCADOPAGO : PaymentVendor::MERCADOPAGO_CARD,
                $preapproval,
                paymentMethod: $paymentMethod,
                paidAt: now()->toImmutable()
            );

            // Comes from wallet balance
            if ($paymentMethod->paymentMethod === PaymentMethod::MERCADOPAGO) {
                if ($subscription->price->trial_days > 0) {
                    $subscription->trial_started_at = now();
                    $subscription->trial_ended_at = now()->addDays($subscription->price->trial_days);
                    $subscription->save();
                }
            }
        }

        return redirect()->route('checkout.callback', $checkout);
    }
}
