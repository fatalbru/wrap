<?php

declare(strict_types=1);

namespace App\Livewire\Checkout;

use App\Enums\PaymentStatus;
use App\Models\Checkout;
use App\Models\Order;
use App\Models\Subscription;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Symfony\Component\HttpFoundation\Response;

class Expired extends Component
{
    #[Locked]
    public Checkout $checkout;

    public function mount(Checkout $checkout): void
    {
        if (!$checkout->expired) {
            $this->redirectRoute('checkout', $checkout);
        }
    }

    public function render()
    {
        return view('livewire.checkout.expired')
            ->title(__('Link expired'));
    }
}
