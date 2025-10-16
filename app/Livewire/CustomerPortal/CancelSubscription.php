<?php

namespace App\Livewire\CustomerPortal;

use App\Models\Subscription;
use Flux\Flux;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;
use Masmerise\Toaster\Toaster;

class CancelSubscription extends Component
{
    #[Locked]
    public ?Subscription $subscription = null;

    public function closeModal(): void
    {
        $this->subscription = null;
        Flux::modal('cancel-subscription')->close();
    }

    #[On('start-cancellation')]
    public function openModal(Subscription $subscription): void
    {
        $this->subscription = $subscription;
        Flux::modal('cancel-subscription')->show();
    }

    public function cancelSubscription(\App\Actions\CancelSubscription $cancelSubscription): void
    {
        if (! $this->subscription->cancelable) {
            Toaster::error(__('Subscription cannot be cancelled.'));

            return;
        }

        $cancelSubscription->handle($this->subscription);

        Toaster::success(__('Subscription cancelled.'));

        $this->closeModal();
    }

    public function render()
    {
        return view('livewire.customer-portal.cancel-subscription');
    }
}
