<?php

namespace App\Listeners\MercadoPago\Payments;

use App\Events\MercadoPago\WebhookReceived;
use App\Models\Application;
use App\Models\Order;
use App\Models\Subscription;
use App\PaymentStatus;
use App\PaymentVendor;
use App\Services\MercadoPago\Payment as PaymentService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class HandlePaymentCreated implements ShouldQueue
{
    use Queueable;

    public function __construct(private readonly PaymentService $paymentService)
    {
    }

    /**
     * @throws \Throwable
     */
    public function handle(WebhookReceived $event): void
    {
        if ($event->getAction() !== 'payment.created') {
            Log::debug(__CLASS__, [
                'message' => 'Only payment.created or created.subscription_authorized_payment events handled',
            ]);

            return;
        }

        $payment = null;

        $application = Application::where('vendor_id', data_get($event->getPayload(), 'application_id'))->first();

        if (blank($application)) {
            foreach (Application::where('vendor_secondary_id', data_get($event->getPayload(), 'user_id'))->get() as $application) {
                if (filled($payment)) {
                    break;
                }
                try {
                    $payment = $this->paymentService->get($application, data_get($event->getPayload(), 'data_id'));
                } catch (RequestException $exception) {
                    continue;
                }
            }
        } else {
            $payment = $this->paymentService->get($application, data_get($event->getPayload(), 'data_id'));
        }

        $modelPrefix = config('mrr.ksuid_prefixes');

        $externalReference = data_get($payment, 'external_reference');
        $payerId = data_get($payment, 'payer.id');
        $description = data_get($payment, 'description');

        $modelClass = null;
        $model = null;

        if ($description === 'Recurring payment validation') {
            Log::debug(__CLASS__, [
                'message' => 'Ignoring recurring payment validation event',
            ]);

            return;
        }

        if (filled($externalReference)) {
            if (!Str::contains($externalReference, [
                $modelPrefix[class_basename(Subscription::class)],
                $modelPrefix[class_basename(Order::class)],
            ])) {
                Log::debug(__CLASS__, [
                    'message' => 'Only Order/Subscription events handled',
                ]);

                return;
            }
        } else {
            /**
             * - [x] Sub card
             * - [x] Sub wallet
             * - [x] Sub wallet card
             * - [x] Trial card
             * - [x] Trial wallet
             * - [x] Trial wallet card
             * - [x] Order card
             *  - [x] Order wallet
             *  - [x] Order wallet card
             */

            // Is this a sub?
            if (Str::contains($description, ' sub_')) {
                $model = Subscription::findByKsuid(Str::of($description)->explode(' ')->last());
            }
        }

        if (blank($modelClass)) {
            $modelClass = Str::contains($externalReference, $modelPrefix[class_basename(Subscription::class)]) ? Subscription::class : Order::class;
        }

        if (blank($model)) {
            $model = $modelClass::findByKsuid($externalReference);
        }

        $status = PaymentStatus::from(data_get($payment, 'status'));

        $model->webhookLogs()->create([
            'application_id' => $model->application->id,
            'vendor' => PaymentVendor::MERCADOPAGO,
            'payload' => $payment,
        ]);

        $model->payments()->updateOrCreate([
            'vendor_id' => data_get($payment, 'data_id'),
        ], [
            'environment' => $model->environment,
            'status' => $status,
            'customer_id' => $model->customer_id,
            'amount' => data_get($payment, 'transaction_amount'),
            'paid_at' => $status === PaymentStatus::APPROVED ? now() : null,
            'vendor_data' => $payment,
            'payment_vendor' => PaymentVendor::MERCADOPAGO,
            'payment_method' => data_get($payment, 'payment_method_id'),
            'payment_type' => data_get($payment, 'payment_type_id'),
            'card_last_digits' => data_get($payment, 'card.last_four_digits'),
        ]);
    }
}
