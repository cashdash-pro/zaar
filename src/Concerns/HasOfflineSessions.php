<?php

namespace CashDash\Zaar\Concerns;

use CashDash\Zaar\Contracts\ShopifySessionsRepositoryInterface;
use CashDash\Zaar\Dtos\OfflineSessionData;

trait HasOfflineSessions
{
    public function offlineSession(): ?OfflineSessionData
    {
        return app(ShopifySessionsRepositoryInterface::class)
            ->findOffline(
                $this->{config('zaar.repositories.shopify.shop_domain_column')
                });
    }
}
