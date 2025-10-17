<?php

declare(strict_types=1);

namespace App\View\Components\Checkout;

use App\Enums\FrequencyType;
use App\Enums\ProductType;
use App\Models\Checkout;
use App\Models\OrderItem;
use App\Models\Subscription;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\View\Component;

class PriceDisplay extends Component
{
    public int|float $total = 0;

    public int $trialDays = 0;

    public ?FrequencyType $frequencyType = null;

    public ProductType $productType = ProductType::ORDER;

    public Collection $items;

    public string $title;

    public function __construct(public Checkout $checkout)
    {
        $this->items = collect([]);

        if ($this->checkout->checkoutable instanceof Subscription) {
            $this->productType = ProductType::SUBSCRIPTION;
            $this->trialDays = $this->checkout->checkoutable->price->trial_days;
            $this->frequencyType = $this->checkout->checkoutable->price->frequency;
            $this->title = __('Subscribe to :price', ['price' => $this->checkout->checkoutable->price->name]);
            $this->total = $this->checkout->checkoutable->price->price;
        } else {
            $this->items = $this->checkout->checkoutable->items->map(fn (OrderItem $orderItem) => [
                'name' => $orderItem->price->name,
                'quantity' => $orderItem->quantity,
                'unit_price' => $orderItem->price->price,
                'subtotal' => $orderItem->price->price * $orderItem->quantity,
            ]);
            $this->total = $this->items->sum('subtotal');
            $this->title = __('Order #:id', [
                'id' => Str::of($this->checkout->checkoutable_id)->padLeft(4, 0),
            ]);
        }
    }

    public function render(): View|Closure|string
    {
        return view('components.checkout.price-display');
    }
}
