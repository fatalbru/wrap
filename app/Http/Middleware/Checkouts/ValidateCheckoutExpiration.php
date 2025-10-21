<?php

namespace App\Http\Middleware\Checkouts;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidateCheckoutExpiration
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $checkout = $request->route('checkout');

        if ($checkout->expired) {
            return redirect()->route('checkout.expired', $checkout);
        }

        return $next($request);
    }
}
