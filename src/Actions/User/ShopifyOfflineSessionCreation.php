<?php

namespace CashDash\Zaar\Actions\User;

use CashDash\Zaar\Actions\TokenExchangeAuth\ExchangeForSessionData;
use CashDash\Zaar\Concerns\Actions\AsObject;
use CashDash\Zaar\Concerns\ShopifySessionsRepositoryInterface;
use CashDash\Zaar\Dtos\SessionToken;
use CashDash\Zaar\Events\ShopifyOfflineSessionCreated;

class ShopifyOfflineSessionCreation
{
    use AsObject;

    public function __construct(
        private ShopifySessionsRepositoryInterface $repository
    ) {}

    public function handle(string $bearer_token, SessionToken $sessionToken)
    {
        $sessionData = ExchangeForSessionData::make()->handleOffline($bearer_token, $sessionToken);

        $this->repository->createOffline($sessionData);

        event(new ShopifyOfflineSessionCreated($sessionData));

        return $sessionData;
    }
}
