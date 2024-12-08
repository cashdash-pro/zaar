<?php

namespace CashDash\Zaar\Events;

use Illuminate\Database\Eloquent\Model;

class ShopifyTenantLoaded
{
    public function __construct(public Model $shopify) {}
}
