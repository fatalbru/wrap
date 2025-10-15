<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\Application;
use App\Models\Checkout;
use App\Models\Order;
use App\Models\Price;
use App\Models\Product;
use App\Models\Subscription;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Psr\Http\Message\RequestInterface;
use Tuupola\Ksuid;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if (Str::contains(config('app.url'), 'https')) {
            URL::forceHttps();
        }

        Relation::enforceMorphMap([
            1 => Subscription::class,
            2 => Checkout::class,
            3 => User::class,
            4 => Product::class,
            5 => Price::class,
            6 => Order::class,
        ]);

        Carbon::setLocale(config('app.locale'));

        Str::macro('ksuid', fn(string $prefix) => sprintf('%s_%s', $prefix, bin2hex((new Ksuid())->payload())));

        Http::macro('mercadopago', function (Application $application) {
            return Http::baseUrl('https://api.mercadopago.com')
                ->withRequestMiddleware(function (RequestInterface $request) {
                    if (app()->isLocal()) {
                        logger($request->getUri(), [json_decode($request->getBody()->getContents())]);
                    }

                    return $request;
                })
                ->withToken($application->private_key)
                ->asJson();
        });

        Event::listen('eloquent.creating: *', function (string $event, array $payload): void {
            /** @var Model $model */
            $model = $payload[0];

            if (filled($prefix = config('mrr.ksuid_prefixes.' . class_basename(get_class($model))))) {
                $class = get_class($model);
                $model->ksuid = $class::generateKsuid($prefix);
            }
        });
    }
}
