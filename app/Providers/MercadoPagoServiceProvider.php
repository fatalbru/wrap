<?php

namespace App\Providers;

use App\Models\Application;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\ServiceProvider;
use Psr\Http\Message\RequestInterface;

class MercadoPagoServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
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
    }
}
