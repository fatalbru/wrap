<?php

declare(strict_types=1);

namespace App\Livewire\Checkout;

use App\Actions\Orders\PayOrder;
use App\Actions\Subscriptions\Subscribe;
use App\DTOs\PaymentMethodDto;
use App\Enums\PaymentMethod;
use App\Enums\PaymentVendor;
use App\Enums\ProductType;
use App\Models\Application;
use App\Models\Checkout;
use App\Models\Payment;
use App\Models\Subscription;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Masmerise\Toaster\Toaster;
use Throwable;

class CardPayment extends Component
{
    #[Locked]
    public Checkout $checkout;

    public array $card = [];

    #[Computed]
    public function buttonLabel(): string
    {
        if ($this->checkout->checkoutable instanceof Subscription) {
            return $this->checkout->checkoutable->price->trial_days > 0 ? 'Start trial' : 'Subscribe';
        }

        return 'Pay';
    }

    /**
     * @throws Throwable
     */
    public function pay(PayOrder $payOrder, Subscribe $createSubscription): void
    {
        $this->validate([
            'card.token' => ['required'],
        ]);

        $handler = $this->checkout->type === ProductType::SUBSCRIPTION ? $createSubscription : $payOrder;

        /** @var Payment $payment */
        $payment = DB::transaction(fn () => $handler->handle(
            $this->checkout,
            new PaymentMethodDto([
                'paymentMethod' => PaymentMethod::CARD,
                ...$this->card,
            ]),
        ));

        if (! $payment->isSuccessful()) {
            $this->failed($payment->decline_reason);

            return;
        }

        $this->completed();
    }

    private function failed(?string $message): void
    {
        $message ??= __('Something went wrong.');
        $this->dispatch('failed', errorMessage: $message)->self();
        Toaster::error($message);
    }

    #[Computed]
    public function publicKey(): string
    {
        return Application::assign(
            PaymentVendor::MERCADOPAGO_CARD,
            $this->checkout->environment,
            $this->checkout->type
        )->public_key;
    }

    private function completed()
    {
        $this->redirectRoute('checkout.complete', [
            'checkout' => $this->checkout,
            'shouldRedirect' => true,
        ]);
    }

    public function render()
    {
        return view('livewire.checkout.card-payment');
    }
}
