<?php

namespace CashDash\Zaar\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class FixReferrerMiddleware
{
    public function handle(Request $request, Closure $next): mixed
    {
        $referrer = $request->header('X-Referrer') ?? $request->header('Referer');
        $originalReferrer = $request->header('referer');
        $request->headers->set('Referer', $referrer);
        $request->headers->set('og-referrer', $originalReferrer);

        return $next($request);
    }
}
