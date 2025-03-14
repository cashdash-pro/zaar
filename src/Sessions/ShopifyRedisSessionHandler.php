<?php

namespace CashDash\Zaar\Sessions;

use CashDash\Zaar\Actions\TokenExchangeAuth\DecodeShopifySessionToken;
use CashDash\Zaar\Actions\TokenExchangeAuth\GetTokenFromRequest;
use CashDash\Zaar\Dtos\SessionToken;
use Illuminate\Session\CacheBasedSessionHandler;

class ShopifyRedisSessionHandler extends CacheBasedSessionHandler
{
    public ?SessionToken $sessionToken = null;

    private function getToken(): ?SessionToken
    {
        if ($this->sessionToken) {
            return $this->sessionToken;
        }

        $bearerToken = GetTokenFromRequest::make()->handle(request());
        if ($bearerToken && $auth = DecodeShopifySessionToken::make()->handle($bearerToken)) {
            return $this->sessionToken = $auth;
        }

        return null;
    }

    public function read($sessionId): string
    {
        if ($auth = $this->getToken()) {
            return $this->cache->get($auth->sub);
        }

        return $this->cache->get($sessionId);
    }

    public function write($sessionId, $data): bool
    {
        if ($auth = $this->getToken()) {
            return $this->cache->put($auth->sub, $data);
        }

        return $this->cache->put($sessionId, $data);
    }
}
