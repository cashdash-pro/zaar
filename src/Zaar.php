<?php

namespace CashDash\Zaar;

use CashDash\Zaar\Actions\Creation\ShopifyOfflineSessionCreation;
use CashDash\Zaar\Actions\Creation\ShopifyOnlineSessionCreation;
use CashDash\Zaar\Actions\TokenExchangeAuth\DiscoverEmbeddedAuth;
use CashDash\Zaar\Contracts\ProvidesOfflineSession;
use CashDash\Zaar\Contracts\ProvidesOnlineSessions;
use CashDash\Zaar\Contracts\ShopifyRepositoryInterface;
use CashDash\Zaar\Contracts\ShopifySessionsRepositoryInterface;
use CashDash\Zaar\Dtos\EmbeddedAuthData;
use CashDash\Zaar\Dtos\OfflineSessionData;
use CashDash\Zaar\Dtos\OnlineSessionData;
use CashDash\Zaar\Dtos\SessionData;
use CashDash\Zaar\Events\OfflineSessionLoaded;
use CashDash\Zaar\Events\OnlineSessionLoaded;
use CashDash\Zaar\Events\SessionAuthenticated;
use CashDash\Zaar\Events\ShopifyTenantLoaded;
use CashDash\Zaar\Exceptions\ShopifySessionNotStartedException;
use Webmozart\Assert\Assert;

class Zaar
{
    /**
     * @var callable|null
     */
    public static $createUserCallback = null;

    /**
     * @var callable|null
     */
    public static $findUserCallback = null;

    /**
     * @var callable|null
     */
    public static $resolveExternalRequest = null;

    /**
     * @var null|callable
     */
    public static $createShopifyCallback = null;

    /**
     * @var null|callable
     */
    public static $shouldImpersonateShopify = null;

    /**
     * Supply a callback that takes an OnlineSessionData object and returns a user object.
     */
    public static function createUserUsing(callable $callback): void
    {
        self::$createUserCallback = $callback;
    }

    public static function createShopifyUsing(callable $callback): void
    {
        self::$createShopifyCallback = $callback;
    }

    /**
     * Supply a callback that takes an OnlineSessionData object and returns a user object.
     */
    public static function findUserUsing(callable $callback): void
    {
        //         Zaar::findUserUsing(function (OnlineSessionData $session) {
        //             return User::where('email', $session->email)->first();
        //         });

        self::$findUserCallback = $callback;
    }

    public static function resolveExternalRequestsUsing(callable $callback): void
    {
        //         Zaar::resolveExternalRequestsUsing(function (Request $request) {
        // you can use the request to determine the shop domain
        // i.e. return $request->get('shop');
        // i.e return $request->header('x-shopify-shop-domain');
        // i.e return $request->route('shop');
        //         });

        self::$resolveExternalRequest = $callback;
    }

    public static function shouldImpersonateShopify(callable $callback): void
    {
        // here you'll get given the user, shopify and request
        // you can return a different shopify that implements ProvidesOfflineSession
        // that will be used to start the session

        self::$shouldImpersonateShopify = $callback;
    }

    public static function sessionType(): SessionType
    {
        return config('zaar.shopify_app.session_type');
    }

    public static function session(): ?SessionData
    {
        $online = app()->has(OnlineSessionData::class) ? app(OnlineSessionData::class) : null;
        $offline = app()->has(OfflineSessionData::class) ? app(OfflineSessionData::class) : null;

        return SessionData::merge($online, $offline);
    }

    /**
     * @throws ShopifySessionNotStartedException
     */
    public static function offlineSession(): OfflineSessionData
    {
        if (self::sessionType() === SessionType::ONLINE) {
            throw new \InvalidArgumentException('You have not configured your app to use offline sessions');
        }

        if (! app()->has(OfflineSessionData::class)) {
            throw new ShopifySessionNotStartedException('No shopify session has been resolved for this request');
        }

        return app(OfflineSessionData::class);
    }

    /**
     * @throws ShopifySessionNotStartedException
     */
    public static function onlineSession(): OnlineSessionData
    {
        if (! app()->has(OnlineSessionData::class)) {
            throw new ShopifySessionNotStartedException('An online session has not been loaded');
        }

        return app(OnlineSessionData::class);
    }

    public static function sessionStarted(): bool
    {
        return app()->has(OnlineSessionData::class) || app()->has(OfflineSessionData::class);
    }

    public static function isEmbedded(): bool
    {
        if (app()->has(EmbeddedAuthData::class)) {
            return true;
        }

        $auth = DiscoverEmbeddedAuth::make()->handle(request());

        if ($auth) {
            return true;
        }

        if (request()->query('embedded') === '1') {
            return true;
        }

        if (request()->header('sec-fetch-dest') === 'iframe'
            && request()->header('sec-fetch-mode') === 'navigate') {
            return true;
        }

        return false;
    }

    public static function clearExpiredSessionsAndReauthenticate(string $domain)
    {
        $repo = app(ShopifySessionsRepositoryInterface::class);
        $repo->deleteAll($domain);

        if (! app()->has(EmbeddedAuthData::class)) {
            return false;
        }

        $auth = app(EmbeddedAuthData::class);

        // Create new offline session
        $offline = ShopifyOfflineSessionCreation::make()->handle($auth);
        app()->instance(OfflineSessionData::class, $offline);

        // Create online session if token exists
        $online = null;
        if ($auth->session_token) {
            $online = ShopifyOnlineSessionCreation::make()->handle($auth);
            app()->instance(OnlineSessionData::class, $online);
        }

        // Create merged session data
        $sessionData = SessionData::merge($online, $offline);

        // Fire events
        if ($online) {
            event(new OnlineSessionLoaded($online));
        }

        event(new OfflineSessionLoaded($offline));

        $shopify = app(ShopifyRepositoryInterface::class)->find($domain);
        Assert::notNull($shopify, 'Shopify model not found');

        event(new SessionAuthenticated($sessionData, $shopify, auth()->user()));

        return true;
    }

    public static function startSessionManually(ProvidesOfflineSession|string $shopifyOrDomain, ?ProvidesOnlineSessions $user = null): bool
    {
        if (is_string($shopifyOrDomain)) {
            $shopifyOrDomain = app(ShopifyRepositoryInterface::class)->find($shopifyOrDomain);
        }

        Assert::isInstanceOf($shopifyOrDomain, ProvidesOfflineSession::class, 'Shopify model must implement ProvidesOfflineSessions');

        $session = $shopifyOrDomain->offlineSession();
        if (! $session) {
            return false;
        }

        $sessionData = SessionData::merge(null, $session);

        app()->instance(OfflineSessionData::class, $session);

        event(new OfflineSessionLoaded($session));
        event(new ShopifyTenantLoaded($shopifyOrDomain));
        event(new SessionAuthenticated($sessionData, $shopifyOrDomain, $user));

        return true;
    }


}
