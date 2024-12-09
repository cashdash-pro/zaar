<?php

namespace CashDash\Zaar\Actions\Creation;

use CashDash\Zaar\Actions\TokenExchangeAuth\ExchangeForSessionData;
use CashDash\Zaar\Concerns\Actions\AsObject;
use CashDash\Zaar\Contracts\ShopifySessionsRepositoryInterface;
use CashDash\Zaar\Dtos\EmbeddedAuthData;
use CashDash\Zaar\Events\ShopifyOnlineSessionCreated;

class ShopifyOnlineSessionCreation
{
    use AsObject;

    public function __construct(
        private ShopifySessionsRepositoryInterface $repository
    ) {}

    public function handle(EmbeddedAuthData $data)
    {
        $sessionData = ExchangeForSessionData::make()->handleOnline($data->bearer_token, $data->session_token);

        $this->repository->createOnline($sessionData);

        event(new ShopifyOnlineSessionCreated($sessionData));

        return $sessionData;
    }
}
