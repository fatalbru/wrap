<?php

declare(strict_types=1);

namespace App\Livewire\Checkout;

use App\Actions\PayOrder;
use App\Actions\Subscribe;
use App\Dtos\MercadoPago\Cards\TemporaryCardDto;
use App\Models\Application;
use App\Models\Checkout;
use App\Models\Payment;
use App\Models\Subscription;
use App\PaymentVendor;
use App\ProductType;
use Masmerise\Toaster\Toaster;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;

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

    public function pay(PayOrder $payOrder, Subscribe $createSubscription): void
    {
        $this->validate([
            'card.token' => ['required'],
        ]);

        $handler = $this->checkout->type === ProductType::SUBSCRIPTION ? $createSubscription : $payOrder;

        /** @var Payment $payment */
        $payment = Cache::lock("checkout:{$this->checkout->ksuid}", 10)
            ->block(3, fn () => DB::transaction(fn () => $handler->handle(
                $this->checkout,
                TemporaryCardDto::make($this->card),
            )));

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
