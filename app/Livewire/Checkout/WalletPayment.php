<?php

declare(strict_types=1);

namespace App\Livewire\Checkout;

use App\Actions\CreatePreferenceLink;
use App\Actions\CreateSubscriptionLink;
use App\Enums\ProductType;
use App\Models\Checkout;
use App\Models\Payment;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Masmerise\Toaster\Toaster;

class WalletPayment extends Component
{
    #[Locked]
    public Checkout $checkout;

    public ?string $email = null;

    public function mount()
    {
        $this->email = $this->checkout->customer?->email;
    }

    /**
     * @throws LockTimeoutException
     */
    public function pay(CreatePreferenceLink $createPreferenceLink, CreateSubscriptionLink $createSubscriptionLink): void
    {
        $this->validate([
            'email' => ['required', 'email'],
        ]);

        $handler = $this->checkout->type === ProductType::SUBSCRIPTION ? $createSubscriptionLink : $createPreferenceLink;

        /** @var Payment $payment */
        $response = Cache::lock("checkout:{$this->checkout->id}", 10)
            ->block(3, fn () => DB::transaction(fn () => $handler->handle($this->checkout)));

        if (blank(data_get($response, 'init_point'))) {
            $this->failed(__('Failed to generate Mercadopago link.'));

            return;
        }

        $this->redirect(data_get($response, 'init_point'));
    }

    private function failed(string $message): void
    {
        $this->dispatch('failed', errorMessage: $message)->self();
        Toaster::error($message);
    }

    public function render()
    {
        return view('livewire.checkout.wallet-payment');
    }
}
