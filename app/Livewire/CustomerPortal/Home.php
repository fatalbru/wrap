<?php

namespace App\Livewire\CustomerPortal;

use App\Models\Customer;
use App\Models\Subscription;
use App\PaymentStatus;
use App\SubscriptionStatus;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Context;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;

class Home extends Component
{
    #[Locked]
    public Customer $customer;

    #[Locked]
    public ?string $backUrl = null;

    public function mount()
    {
        $this->customer = Context::get('portal.customer');
        $this->backUrl = Context::get('portal.back_url');
    }

    #[Computed]
    public function subscriptions(): Collection
    {
        return $this->customer
            ->subscriptions()
            ->where('status', '!=', SubscriptionStatus::PENDING)
            ->get();
    }

    #[Computed]
    public function payments(): Collection
    {
        return $this->customer
            ->payments()
            ->whereIn('status', [
                PaymentStatus::APPROVED,
                PaymentStatus::CANCELLED,
                PaymentStatus::REFUNDED
            ])
            ->get();
    }

    public function startCancellation(Subscription $subscription): void
    {
        $this->dispatch('start-cancellation', compact('subscription'));
    }

    public function render()
    {
        return view('livewire.customer-portal.home')
            ->title(config('app.name'));
    }
}
