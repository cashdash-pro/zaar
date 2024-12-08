<?php

namespace CashDash\Zaar\Auth;

use CashDash\Zaar\Actions\TokenExchangeAuth\DecodeSessionToken;
use CashDash\Zaar\Actions\TokenExchangeAuth\GetTokenFromRequest;
use CashDash\Zaar\Actions\TokenExchangeAuth\LoadOfflineSession;
use CashDash\Zaar\Actions\TokenExchangeAuth\LoadOnlineSession;
use CashDash\Zaar\Actions\User\ShopifyCreation;
use CashDash\Zaar\Actions\User\UserCreation;
use CashDash\Zaar\Concerns\ShopifyRepositoryInterface;
use CashDash\Zaar\Concerns\UserRepositoryInterface;
use CashDash\Zaar\Dtos\SessionData;
use CashDash\Zaar\Events\SessionAuthenticated;
use CashDash\Zaar\Events\ShopifyTenantLoaded;
use CashDash\Zaar\SessionType;
use CashDash\Zaar\Zaar;
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

        $token = DecodeSessionToken::make()->handle($bearer_token);

        app()->instance(SessionData::class, $token);

        return $token;
    }

    /**
     * Retrieve the authenticated user for the incoming request.
     *
     * @return mixed
     */
    public function __invoke(Request $request)
    {

        foreach (Arr::wrap(config('zaar.guard', 'web')) as $guard) {
            if ($user = $this->auth->guard($guard)->user()) {
                return $user;
            }
        }

        if (! $bearer_token = GetTokenFromRequest::make()->handle($request)) {
            return null;
        }

        $sessionToken = DecodeSessionToken::make()->handle($bearer_token);

        if (! $sessionToken) {
            return null;
        }

        $sessionData = LoadOnlineSession::make()->handle($bearer_token, $sessionToken);

        $user = app(UserRepositoryInterface::class)->find($sessionData);

        if (! $user) {
            $user = UserCreation::make()->handle($sessionData);
        }

        if (! $this->hasValidProvider($user)) {
            return null;
        }

        if (Zaar::sessionType() === SessionType::OFFLINE) {
            $offline = LoadOfflineSession::make()->handle($bearer_token, $sessionToken);
            $sessionData = SessionData::merge($sessionData, $offline);
        }

        $shopify = app(ShopifyRepositoryInterface::class)->find($sessionData->shop);
        if (! $shopify) {
            $shopify = ShopifyCreation::make()->handle($sessionData);
        }

        event(new ShopifyTenantLoaded($shopify));

        event(new SessionAuthenticated($sessionData));

        return $user;
    }

    /**
     * Determine if the shopify model matches the provider's model type.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $user
     * @return bool
     */
    protected function hasValidProvider($user)
    {
        if (is_null($this->provider)) {
            return true;
        }

        $model = config("auth.providers.{$this->provider}.model");

        return $user instanceof $model;
    }
}
