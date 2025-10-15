<?php

declare(strict_types=1);

namespace App\Livewire\Checkout;

use App\Models\Checkout;
use App\Models\Order;
use App\Models\Subscription;
use App\PaymentStatus;
use Livewire\Attributes\Locked;
use Livewire\Component;

class Callback extends Component
{
    #[Locked]
    public Checkout $checkout;

    public function checkPayments(): void
    {
        /** @var Order|Subscription $checkoutable */
        $checkoutable = $this->checkout->checkoutable;

        if (filled($this->checkout->completed_at)) {
            $this->redirectRoute('checkout.complete', $this->checkout);

            return;
        }

        if (
            $checkoutable->payments()->where('status', '!=', PaymentStatus::APPROVED)->exists() &&
            blank($checkoutable->price?->trial_days)
        ) {
            $this->redirect(route('checkout', [
                'checkout' => $this->checkout,
                'error' => 1,
            ]));
        }
    }

    public function render()
    {
        return view('livewire.checkout.callback')
            ->title(__('Processing payment, please wait...'));
    }
}
