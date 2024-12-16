<?php

namespace CashDash\Zaar\Http\Middleware;

use CashDash\Zaar\Actions\TokenExchangeAuth\DecodeSessionToken;
use CashDash\Zaar\Actions\TokenExchangeAuth\GetTokenFromRequest;
use CashDash\Zaar\Zaar;
use Closure;
use Illuminate\Http\Request;

class ReauthenticateEmbeddedRequestsMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (!Zaar::isEmbedded()) {
            return $next($request);
        }


        $token = GetTokenFromRequest::make()->handle($request);
        if ($token) {
            if (DecodeSessionToken::make()->handle($token) !== null) {
                return $next($request);
            }
            // the token is likely expired, so this will still be required
        }

        return redirect(self::getRedirectUrl($request), 303);
    }

    public static function getRedirectUrl(Request $request): string
    {
        $baseUrl = 'https://'.$request->getHost();
        $currentPath = $request->path();

        $queryParams = $request->query();
        unset($queryParams['id_token']);

        $queryParams['redirect_url'] = $baseUrl.'/'.$currentPath.(! empty($queryParams) ? '?'.http_build_query($queryParams) : '');

        return '/auth/token/reauthenticate?'.http_build_query($queryParams);
    }
}
