<?php

namespace CashDash\Zaar\Actions\Resolvers;

use CashDash\Zaar\Concerns\Actions\AsObject;
use CashDash\Zaar\Contracts\ProvidesShopifySessions;
use CashDash\Zaar\Dtos\OnlineSessionData;
use Illuminate\Contracts\Auth\Authenticatable;

class ResolveOnlineSession
{
    use AsObject;

    public function handle(?Authenticatable $user): ?OnlineSessionData
    {
        if ($user instanceof ProvidesShopifySessions) {
            return $user->onlineSession();
        }

        // TODO: support more resolvers?

        return null;
    }
}
