<?php

declare(strict_types=1);

namespace App\View\Components\Checkout;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class QrCode extends Component
{
    public function __construct(public string $url) {}

    public function render(): View|Closure|string
    {
        return view('components.checkout.qr-code');
    }
}
