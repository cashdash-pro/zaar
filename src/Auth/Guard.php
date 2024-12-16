<?php

namespace CashDash\Zaar\Auth;

use CashDash\Zaar\Actions\TokenExchangeAuth\DecodeShopifySessionToken;
use CashDash\Zaar\Actions\TokenExchangeAuth\GetTokenFromRequest;
use CashDash\Zaar\Auth\Strategies\EmbeddedStrategy;
use CashDash\Zaar\Auth\Strategies\ExternalStrategy;
use CashDash\Zaar\Contracts\AuthFlow;
use CashDash\Zaar\Dtos\SessionData;
use CashDash\Zaar\Http\Middleware\ReauthenticateEmbeddedRequestsMiddleware;
use CashDash\Zaar\SessionType;
use CashDash\Zaar\Zaar;
use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class Guard
{
    /**
     * The authentication factory implementation.
     */
    protected AuthFactory $auth;

    /**
     * The provider name.
     */
    protected string $provider;

    /**
     * Create a new guard instance.
     */
    public function __construct(AuthFactory $auth, $provider = null)
    {
        $this->auth = $auth;
        $this->provider = $provider;
    }

    private function getSessionToken(Request $request): ?SessionData
    {
        if (app()->has(SessionData::class)) {
            return app(SessionData::class);
        }

        $bearer_token = GetTokenFromRequest::make()->handle($request);
        if (! $bearer_token) {
            return null;
        }

        $token = DecodeShopifySessionToken::make()->handle($bearer_token);

        app()->instance(SessionData::class, $token);

        return $token;
    }

    /**
     * Retrieve the authenticated user for the incoming request.
     */
    public function __invoke(Request $request): ?Authenticatable
    {
        $user = null;
        foreach (Arr::wrap(config('zaar.guards', 'web')) as $guard) {
            if ($user = $this->auth->guard($guard)->user()) {
                break;
            }
        }

        /** @var ExternalStrategy|EmbeddedStrategy $auth */
        $auth = Zaar::isEmbedded() ? app(EmbeddedStrategy::class) : app(ExternalStrategy::class);

        $user = $auth
            ->withOnlineSession($request, $user)
            ->withUser()
            ->withDomain()
            ->when(Zaar::sessionType() === SessionType::OFFLINE, fn (AuthFlow $auth) => $auth->withOfflineSession())
            ->mergeSessions()
            ->bindData()
            ->withShopifyModel()
            ->dispatchEvents()
            ->getUser();

        if (! $user) {
            // we can fix this
            if (Zaar::isEmbedded()) {
                Authenticate::redirectUsing(function () {
                    return ReauthenticateEmbeddedRequestsMiddleware::getRedirectUrl(request());
                });
            } else {
                Authenticate::redirectUsing(function () use ($auth) {
                    $domain = $auth->withDomain()->getDomain();
                    if ($domain) {
                        return route('auth.shopify', ['domain' => $domain]);
                    } else {
                        return route('login');
                    }
                });
            }
        }

        return $user;
    }
}
