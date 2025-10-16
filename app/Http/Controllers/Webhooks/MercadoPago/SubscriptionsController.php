<?php

namespace App\Http\Controllers\Webhooks\MercadoPago;

use App\Actions\RegisterWebhookLog;
use App\Exceptions\IdempotencyOverlap;
use App\Http\Controllers\Controller;
use App\Models\Checkout;
use App\Models\Subscription;
use App\PaymentProvider;
use App\PaymentStatus;
use App\PaymentVendor;
use App\SubscriptionStatus;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;
use App\Services\MercadoPago\Subscription as SubscriptionService;

class SubscriptionsController extends Controller
{
    /**
     * Malformed/abusive requests have no mercy here
     * @param string $signature
     * @param SubscriptionService $subscriptionService
     * @param RegisterWebhookLog $registerWebhookLog
     * @return RedirectResponse
     * @throws IdempotencyOverlap
     */
    public function preapprovalCallback(
        string              $signature,
        SubscriptionService $subscriptionService,
        RegisterWebhookLog  $registerWebhookLog
    )
    {
        $signature = decrypt($signature);

        $validator = Validator::make($signature, [
            'checkout_id' => ['required', 'exists:checkouts,id'],
            'subscription_id' => ['required', 'exists:subscriptions,id'],
            'idempotency' => ['required', 'unique:webhook_logs,idempotency']
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

        $registerWebhookLog->handle(
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

        $registerWebhookLog->handle($subscription, $preapproval, paymentProvider: PaymentProvider::MERCADOPAGO);

        if ($status === SubscriptionStatus::AUTHORIZED && blank($subscription->started_at)) {
            $checkout->touch('completed_at');

            $subscription->update([
                'status' => SubscriptionStatus::AUTHORIZED,
                'started_at' => now()
            ]);

            $payment_method_id = data_get($preapproval, 'payment_method_id');

            $subscription->payments()->create([
                'status' => PaymentStatus::APPROVED,
                'customer_id' => $subscription->customer_id,
                'amount' => $subscription->price->has_trial ? 0 : $subscription->price->price,
                'paid_at' => now(),
                'payment_vendor' => $payment_method_id === 'account_money' ? PaymentVendor::MERCADOPAGO : PaymentVendor::MERCADOPAGO_CARD,
                'vendor_data' => $preapproval,
                'payment_method' => 'account_money',
                'payment_type' => 'account_money',
            ]);

            // Comes from wallet balance
            if ($payment_method_id === 'account_money') {
                if ($subscription->price->trial_days > 0) {
                    $subscription->update([
                        'trial_started_at' => now(),
                        'trial_ended_at' => now()->addDays($subscription->price->trial_days),
                    ]);
                }
            }
        }

        return redirect()->route('checkout.callback', $checkout);
    }
}
