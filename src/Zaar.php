<?php

namespace CashDash\Zaar;

use CashDash\Zaar\Actions\TokenExchangeAuth\DecodeSessionToken;
use CashDash\Zaar\Actions\TokenExchangeAuth\GetTokenFromRequest;
use CashDash\Zaar\Dtos\OfflineSessionData;
use CashDash\Zaar\Dtos\OnlineSessionData;
use CashDash\Zaar\Dtos\SessionData;
use CashDash\Zaar\Dtos\SessionToken;

class Zaar
{
    public static $createUserCallback = null;

    public static $findUserCallback = null;

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
        // Sample usage:

        //         Zaar::findUserUsing(function (OnlineSessionData $session) {
        //             return User::where('email', $session->email)->first();
        //         });

        self::$findUserCallback = $callback;
    }

    public static function sessionType(): SessionType
    {
        return config('zaar.shopify_app.session_type');
    }

    public static function session(): SessionData
    {
        if (self::sessionType() === SessionType::OFFLINE) {
            return SessionData::merge(self::onlineSession(), self::offlineSession());
        } else {
            return self::onlineSession();
        }
    }

    public static function offlineSession(): OfflineSessionData
    {
        if (self::sessionType() === SessionType::ONLINE) {
            throw new \InvalidArgumentException('You must configure Zaar to store offline session tokens');
        }

        return app(OfflineSessionData::class);
    }

    public static function onlineSession(): OnlineSessionData
    {
        return app(OnlineSessionData::class);
    }

    public static function sessionStarted(): bool
    {
        return app()->has(OnlineSessionData::class);
    }

    public static function isEmbedded(): bool
    {
        if (app()->has(SessionData::class)) {
            return true;
        }

        $bearer_token = GetTokenFromRequest::make()->handle(request());
        if (! $bearer_token) {
            return false;
        }

        $token = DecodeSessionToken::make()->handle($bearer_token);
        if (! $token) {
            return false;
        }

        app()->instance(SessionToken::class, $token);

        return true;
    }
}
