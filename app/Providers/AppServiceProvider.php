<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\Checkout;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Price;
use App\Models\Product;
use App\Models\Refund;
use App\Models\Subscription;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->enforceHttps();
        $this->enforceMorphMap();
        $this->enforceLocale();
    }

    private function enforceHttps(): void
    {
        if (Str::contains(config('app.url'), 'https')) {
            URL::forceHttps();
        }
    }

    private function enforceMorphMap(): void
    {
        Relation::enforceMorphMap(
            collect([
                Customer::class,
                Payment::class,
                Price::class,
                Product::class,
                Refund::class,
                Subscription::class,
                Checkout::class,
                Order::class,
                User::class,
            ])
                ->mapWithKeys(fn (string $className): array => [
                    strtolower(class_basename($className)) => $className,
                ])
                ->toArray()
        );
    }

    private function enforceLocale(): void
    {
        Carbon::setLocale(config('app.locale'));
    }
}
