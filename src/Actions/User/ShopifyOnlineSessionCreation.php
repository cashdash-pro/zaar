<?php

namespace CashDash\Zaar\Actions\User;

use CashDash\Zaar\Actions\TokenExchangeAuth\ExchangeForSessionData;
use CashDash\Zaar\Concerns\Actions\AsObject;
use CashDash\Zaar\Concerns\ShopifySessionsRepositoryInterface;
use CashDash\Zaar\Dtos\SessionToken;
use CashDash\Zaar\Events\ShopifyOnlineSessionCreated;

class ShopifyOnlineSessionCreation
{
    use AsObject;

    public function __construct(
        private ShopifySessionsRepositoryInterface $repository
    ) {}

    public function handle(string $bearer_token, SessionToken $sessionToken)
    {
        $sessionData = ExchangeForSessionData::make()->handleOnline($bearer_token, $sessionToken);

        $this->repository->createOnline($sessionData);

        event(new ShopifyOnlineSessionCreated($sessionData));

        return $sessionData;
    }
}
