<?php

namespace CashDash\Zaar\Http\Middleware;

use CashDash\Zaar\Zaar;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RemoveCookiesMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        if (Zaar::isEmbedded()) {
            if ($response instanceof Response) {
                // Clear all cookies
                foreach ($response->headers->getCookies() as $cookie) {
                    $response->headers->removeCookie($cookie->getName());
                }
            }
        }

        return $response;
    }
}
