<?php

namespace CashDash\Zaar\Events;

use Illuminate\Foundation\Events\Dispatchable;

class ShopifyCreated
{
    use Dispatchable;

    public function __construct($shopify) {}
}
