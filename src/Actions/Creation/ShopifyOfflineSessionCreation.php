<?php

namespace CashDash\Zaar\Actions\Creation;

use CashDash\Zaar\Actions\TokenExchangeAuth\ExchangeForSessionData;
use CashDash\Zaar\Concerns\Actions\AsObject;
use CashDash\Zaar\Dtos\EmbeddedAuthData;
use CashDash\Zaar\Dtos\OfflineSessionData;

class ShopifyOfflineSessionCreation
{
    use AsObject;

    public function __construct(
    ) {}

    public function handle(EmbeddedAuthData $auth): OfflineSessionData
    {
        $sessionData = ExchangeForSessionData::make()->handleOffline($auth->bearer_token, $auth->session_token);

        app()->instance(OfflineSessionData::class, $sessionData);

        return $sessionData;
    }
}
