<?php

declare(strict_types=1);

namespace App\Livewire\Checkout;

use App\Actions\Providers\MercadoPago\CreatePreferenceLink;
use App\Actions\Providers\MercadoPago\CreateSubscriptionLink;
use App\Enums\ProductType;
use App\Models\Checkout;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Masmerise\Toaster\Toaster;
use Throwable;

class WalletPayment extends Component
{
    #[Locked]
    public Checkout $checkout;

    public ?string $email = null;

    public function mount(): void
    {
        $this->email = $this->checkout->customer?->email;
    }

    /**
     * @throws Throwable
     */
    public function pay(CreatePreferenceLink $createPreferenceLink, CreateSubscriptionLink $createSubscriptionLink): void
    {
        $this->validate([
            'email' => ['required', 'email'],
        ]);

        $handler = $this->checkout->type === ProductType::SUBSCRIPTION ? $createSubscriptionLink : $createPreferenceLink;

        $link = DB::transaction(fn () => $handler->handle(
            $this->checkout,
            $this->email,
        ));

        if (blank($link->init_point)) {
            $this->failed(__('Failed to generate Mercadopago link.'));

            return;
        }

        $this->redirect($link->init_point);
    }

    private function failed(?string $message): void
    {
        $message ??= __('Something went wrong.');
        $this->dispatch('failed', errorMessage: $message)->self();
        Toaster::error($message);
    }

    public function render()
    {
        return view('livewire.checkout.wallet-payment');
    }
}
