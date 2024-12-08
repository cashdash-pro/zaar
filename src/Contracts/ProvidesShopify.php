<?php

namespace CashDash\Zaar\Contracts;

use Illuminate\Database\Eloquent\Model;

interface ProvidesShopify
{
    public static function findForShopifyDomain(string $domain): ?Model;
}
