<?php

namespace CashDash\Zaar\Http\Middleware;

use CashDash\Zaar\Exceptions\ShopifySessionNotStartedException;
use CashDash\Zaar\SessionType;
use CashDash\Zaar\Zaar;
use Closure;
use Illuminate\Http\Request;

class EnsureSessionStartedMiddleware
{
    /**
     * @throws ShopifySessionNotStartedException
     */
    public function handle(Request $request, Closure $next)
    {
        if (! Zaar::sessionStarted()) {
            // if there's no session, this means there was likely no way to determine the shop domain
            //redirect to shop selection page
            // custom logic

            abort(403, 'Session could not be started. Please make sure the shop domain is set.');
        }
        return $next($request);
    }
}
