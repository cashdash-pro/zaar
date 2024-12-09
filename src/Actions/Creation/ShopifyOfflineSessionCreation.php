<?php

namespace CashDash\Zaar\Actions\Creation;

use CashDash\Zaar\Actions\TokenExchangeAuth\ExchangeForSessionData;
use CashDash\Zaar\Concerns\Actions\AsObject;
use CashDash\Zaar\Contracts\ShopifySessionsRepositoryInterface;
use CashDash\Zaar\Dtos\EmbeddedAuthData;
use CashDash\Zaar\Dtos\OfflineSessionData;
use CashDash\Zaar\Events\ShopifyOfflineSessionCreated;

class ShopifyOfflineSessionCreation
{
    use AsObject;

    public function __construct(
        private ShopifySessionsRepositoryInterface $repository
    ) {}

    public function handle(EmbeddedAuthData $auth): OfflineSessionData
    {
        $sessionData = ExchangeForSessionData::make()->handleOffline($auth->bearer_token, $auth->session_token);

        $this->repository->createOffline($sessionData);

        event(new ShopifyOfflineSessionCreated($sessionData));

        return $sessionData;
    }
}
