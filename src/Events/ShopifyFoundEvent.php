<?php

namespace CashDash\Zaar\Events;

use CashDash\Zaar\Contracts\ProvidesShopify;
use Illuminate\Foundation\Events\Dispatchable;

class ShopifyFoundEvent
{
    use Dispatchable;

    public function __construct(ProvidesShopify $shopify) {}
}
