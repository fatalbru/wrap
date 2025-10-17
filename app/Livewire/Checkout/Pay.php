<?php

declare(strict_types=1);

namespace App\Livewire\Checkout;

use App\Enums\ProductType;
use App\Models\Checkout;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Price;
use App\Models\Product;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Url;
use Livewire\Component;

class Pay extends Component
{
    #[Locked]
    public Checkout $checkout;

    #[Url]
    public bool $error = false;

    #[Locked]
    public ?string $errorMessage = null;

    #[Locked]
    public Price $price;

    #[Locked]
    public Product $product;

    #[Locked]
    public ?Customer $customer = null;

    public ?string $email = null;

    public array $card = [];

    #[Locked]
    public bool $completedCheckout = false;

    public function mount(): void
    {
        if (filled($this->checkout->completed_at)) {
            $this->redirectRoute('checkout.complete', $this->checkout);
        }

        $this->customer = $this->checkout->customer;
        $this->email = $this->customer?->email;
        $this->price = $this->checkout->checkoutable instanceof Order ?
            $this->checkout->checkoutable->items->first()->price :
            $this->checkout->checkoutable->price;
        $this->product = $this->price->product;

        if ($this->error) {
            $this->errorMessage = $this->checkout->checkoutable
                ->payments()
                ->latest('updated_at')
                ->first()?->decline_reason;
        }
    }

    #[Computed]
    public function title(): string
    {
        return $this->product->type === ProductType::SUBSCRIPTION ?
            __('Subscribe to :price', ['price' => $this->price->name]) : $this->price->name;
    }

    public function render()
    {
        return view('livewire.checkout.pay')
            ->title($this->title());
    }
}
