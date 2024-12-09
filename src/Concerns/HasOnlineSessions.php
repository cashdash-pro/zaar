<?php

namespace CashDash\Zaar\Concerns;

use CashDash\Zaar\Contracts\ShopifySessionsRepositoryInterface;
use CashDash\Zaar\Dtos\OnlineSessionData;

trait HasOnlineSessions
{
    public function onlineSession(): ?OnlineSessionData
    {
        return app(ShopifySessionsRepositoryInterface::class)->onlineSessionFor($this->{config('zaar.repositories.user.shopify_user_id_column')});
    }
}
