<?php

namespace CashDash\Zaar\Http\Middleware;

use CashDash\Zaar\Zaar;
use Closure;
use Illuminate\Http\Request;

class AddEmbeddedCspHeaderMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        if (Zaar::isEmbedded()) {
            $frameAncestors = 'https://admin.shopify.com';
            if (Zaar::sessionStarted()) {
                $frameAncestors .= ' https://'.Zaar::session()->shop;
            } else {
                $frameAncestors .= ' *.myshopify.com';
            }
            $response->headers->set('Content-Security-Policy', "frame-ancestors $frameAncestors");
        }

        return $response;
    }
}
