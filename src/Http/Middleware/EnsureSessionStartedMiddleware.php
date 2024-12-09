<?php

namespace CashDash\Zaar\Http\Middleware;

use CashDash\Zaar\Zaar;
use Closure;
use Illuminate\Http\Request;
use Webmozart\Assert\Assert;

class EnsureSessionStartedMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        Assert::notNull(auth()->check(), "Session Guard should be used after the Auth middleware");

        if (!Zaar::sessionStarted()) {
            // if there's no session, this means there was likely no way to determine the shop domain
            abort(403, "Session could not be started. Please make sure the shop domain is set.");
        }

        return $next($request);
    }
}
