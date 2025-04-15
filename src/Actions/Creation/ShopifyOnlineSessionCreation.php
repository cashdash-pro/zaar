<?php

namespace CashDash\Zaar\Actions\Creation;

use CashDash\Zaar\Actions\TokenExchangeAuth\ExchangeForSessionData;
use CashDash\Zaar\Concerns\Actions\AsObject;
use CashDash\Zaar\Dtos\EmbeddedAuthData;
use CashDash\Zaar\Dtos\OnlineSessionData;

class ShopifyOnlineSessionCreation
{
    use AsObject;

    public function __construct(
    ) {}

    public function handle(EmbeddedAuthData $data)
    {
        $sessionData = ExchangeForSessionData::make()->handleOnline($data->bearer_token, $data->session_token);

        app()->instance(OnlineSessionData::class, $sessionData);

        return $sessionData;
    }
}
