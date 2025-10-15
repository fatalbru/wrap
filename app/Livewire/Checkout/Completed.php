<?php

declare(strict_types=1);

namespace App\Livewire\Checkout;

use App\Models\Checkout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Url;
use Livewire\Component;

class Completed extends Component
{
    #[Locked]
    public Checkout $checkout;

    #[Url]
    public bool $shouldRedirect = false;

    public function mount()
    {
        if (blank($this->checkout->completed_at)) {
            $this->redirectRoute('checkout', $this->checkout);
        }
    }

    public function render()
    {
        return view('livewire.checkout.completed')
            ->title(config('app.name'));
    }
}
