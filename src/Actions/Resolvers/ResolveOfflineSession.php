<?php

namespace CashDash\Zaar\Actions\Resolvers;

use CashDash\Zaar\Actions\Creation\ShopifyOfflineSessionCreation;
use CashDash\Zaar\Concerns\Actions\AsObject;
use CashDash\Zaar\Dtos\EmbeddedAuthData;
use CashDash\Zaar\Dtos\OfflineSessionData;
use CashDash\Zaar\Exceptions\OfflineSessionNotFoundException;

class ResolveOfflineSession
{
    use AsObject;

    /**
     * @throws OfflineSessionNotFoundException
     */
    public function handle(?EmbeddedAuthData $auth): ?OfflineSessionData
    {
        if ($auth) {
            $session = ShopifyOfflineSessionCreation::make()->handle($auth);
            event(new OfflineSessionCreated($session));

            return $session;
        }
        throw new OfflineSessionNotFoundException;
    }
}
