<?php

namespace CashDash\Zaar\Http\Middleware;

use CashDash\Zaar\Zaar;
use Closure;
use Illuminate\Http\Request;

class SwapSessionStore
{
  
    public function handle(Request $request, Closure $next)
    {
        if (! Zaar::sessionStarted()) {
            return $next($request);
        }

        config(['session.store' => 'shopify']);

        return $next($request);
    }
}

