<?php

declare(strict_types=1);

namespace App\Actions\Providers\MercadoPago;

use App\Actions\Applications\AssignApplication;
use App\Concerns\Action;
use App\Enums\PaymentVendor;
use App\Enums\ProductType;
use App\Models\Price;
use App\Services\MercadoPago\Subscription as SubscriptionService;
use Illuminate\Support\Arr;
use Throwable;

final class CreatePreapprovalPlan extends Action
{
    public function __construct(
        private readonly SubscriptionService $subscriptionService,
        private readonly AssignApplication $assignApplication,
    ) {}

    /**
     * @link https://www.mercadopago.com.ar/developers/en/reference/subscriptions/_preapproval_plan/post
     *
     * @throws Throwable
     */
    public function execute(Price $price): void
    {
        throw_if($price->product->type !== ProductType::SUBSCRIPTION, 'Only subscription products are eligible for preapprovals.');
        throw_if(filled($price->vendor_id), 'Preapproval already configured.');

        $this->lock(function () use ($price): void {
            $payload = [
                'reason' => $price->name,
                'back_url' => config('wrap.site_url'),
                'payment_methods_allowed' => [
                    'payment_types' => [
                        ['id' => 'prepaid_card'],
                        ['id' => 'credit_card'],
                        ['id' => 'debit_card'],
                        ['id' => 'account_money'],
                    ],
                ],
                'auto_recurring' => [
                    'frequency' => $price->frequency->getFrequencyIterations(),
                    'frequency_type' => $price->frequency->getFrequencyApiType(),
                ],
            ];

            if ($price->trial_days > 0) {
                Arr::set($payload, 'auto_recurring.free_trial', [
                    'frequency' => $price->trial_days,
                    'frequency_type' => 'days',
                ]);
            }

            $application = $this->assignApplication->execute(
                $price->product->environment,
                PaymentVendor::MERCADOPAGO_CARD,
                ProductType::SUBSCRIPTION
            );

            $response = $this->subscriptionService->createPreapprovalPlan(
                $application,
                $payload,
            );

            $price->update([
                'vendor_id' => data_get($response, 'id'),
                'vendor' => PaymentVendor::MERCADOPAGO,
            ]);
        }, ...func_get_args());
    }
}
