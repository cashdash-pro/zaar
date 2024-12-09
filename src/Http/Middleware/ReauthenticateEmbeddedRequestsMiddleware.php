<?php

namespace CashDash\Zaar\Http\Middleware;

use CashDash\Zaar\Actions\TokenExchangeAuth\DecodeSessionToken;
use CashDash\Zaar\Actions\TokenExchangeAuth\GetTokenFromRequest;
use Closure;
use Illuminate\Http\Request;

class ReauthenticateEmbeddedRequestsMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (! $this->isEmbedded($request)) {
            return $next($request);
        }

        $token = GetTokenFromRequest::make()->handle($request);
        if ($token) {
            if (DecodeSessionToken::make()->handle($token)) {
                return $next($request);
            }
            // the token is likely expired, so this will still be required
        }

        return redirect($this->getRedirectUrl($request));
    }

    private function getRedirectUrl(Request $request): string
    {
        $baseUrl = 'https://'.$request->getHost();
        $currentPath = $request->path();

        $queryParams = $request->query();
        unset($queryParams['id_token']);

        $queryParams['shopify-reload'] = $baseUrl.'/'.$currentPath.(! empty($queryParams) ? '?'.http_build_query($queryParams) : '');

        return '/auth/token/reauthenticate?'.http_build_query($queryParams);
    }

    private function isEmbedded(Request $request): bool
    {
        return $request->header('Sec-Fetch-Dest') === 'iframe' &&
            $request->header('Sec-Fetch-Mode') === 'navigate' &&
            $request->header('Sec-Fetch-Site') === 'same-origin';
    }
}
