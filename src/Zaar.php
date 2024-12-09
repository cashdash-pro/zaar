<?php

namespace CashDash\Zaar;

use CashDash\Zaar\Actions\TokenExchangeAuth\DiscoverEmbeddedAuth;
use CashDash\Zaar\Dtos\EmbeddedAuthData;
use CashDash\Zaar\Dtos\OfflineSessionData;
use CashDash\Zaar\Dtos\OnlineSessionData;
use CashDash\Zaar\Dtos\SessionData;
use CashDash\Zaar\Exceptions\OnlineSessionNotLoadedException;
use CashDash\Zaar\Exceptions\ShopifySessionNotStartedException;

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
     * Supply a callback that takes an OnlineSessionData object and returns a user object.
     */
    public static function createUserUsing(callable $callback): void
    {
        self::$createUserCallback = $callback;
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

    public static function sessionType(): SessionType
    {
        return config('zaar.shopify_app.session_type');
    }

    /**
     * @throws ShopifySessionNotStartedException
     * @throws OnlineSessionNotLoadedException
     */
    public static function session(): SessionData
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

        return  DiscoverEmbeddedAuth::make()->handle(request()) !== null;
    }
}
