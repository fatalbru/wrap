<?php

namespace App\Http\Middleware\Customers;

use App\Models\Customer;
use Carbon\Carbon;
use Closure;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Context;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class PortalIdentifierContext
{
    public function handle(Request $request, Closure $next): Response
    {
        try {
            $portalIdentifier = decrypt(base64_decode($request->route('portalIdentifier')));

            $validator = Validator::make($portalIdentifier, [
                'expires_at' => ['required', 'date'],
                'customer_id' => ['required', 'exists:customers,id'],
            ]);

            if ($validator->fails()) {
                abort(Response::HTTP_NOT_FOUND);
            }

            if (!app()->isLocal()) {
                if (Carbon::parse(data_get($portalIdentifier, 'expires_at'))->isPast()) {
                    abort(Response::HTTP_NOT_FOUND);
                }
            }

            $customer = Customer::find(data_get($portalIdentifier, 'customer_id'));

            if (blank($customer)) {
                abort(Response::HTTP_NOT_FOUND);
            }

            Context::add('portal.customer', $customer);
            Context::add('portal.back_url', data_get($portalIdentifier, 'back_url'));

            return $next($request);
        } catch (DecryptException $exception) {
            abort(Response::HTTP_NOT_FOUND);
        }
    }
}
