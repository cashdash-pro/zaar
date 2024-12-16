<?php

namespace CashDash\Zaar\Http\Middleware;

use CashDash\Zaar\Zaar;
use Closure;
use Illuminate\Http\Request;

class FixReferrerMiddleware
{
    public function handle(Request $request, Closure $next): mixed
    {
        if (! Zaar::isEmbedded()) {
            return $next($request);
        }

        $referrer = $request->header('X-Referrer') ?? $request->header('Referer');
        $originalReferrer = $request->header('referer');
        $request->headers->set('Referer', $referrer);
        $request->headers->set('og-referrer', $originalReferrer);

        return $next($request);
    }
}
