<?php

namespace CashDash\Zaar\Http\Middleware;

use CashDash\Zaar\Actions\TokenExchangeAuth\DecodeExtensionSessionToken;
use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;

class AuthenticateExtensionRequestMiddleware
{


    /**
     * @throws AuthenticationException
     */
    public function handle(Request $request, Closure $next)
    {
        $auth = $request->header('Authorization');
        $token = self::parseToken($auth);

        if (! $token) {
            throw new AuthenticationException('Unauthenticated.');
        }

        $session = DecodeExtensionSessionToken::make()->handle($token);
        return $next($request);
    }

    private static function parseToken(array|string|null $auth): string|null
    {
        if (is_null($auth)) {
            return null;
        }

        $auth = explode(' ', $auth);
        if (count($auth) !== 2) {
            return null;
        }

        return $auth[1];
    }
}
