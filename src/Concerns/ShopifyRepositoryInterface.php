<?php

namespace CashDash\Zaar\Concerns;

use CashDash\Zaar\Dtos\ShopifyInfo;
use Illuminate\Database\Eloquent\Model;

interface ShopifyRepositoryInterface
{
    public function updateOrCreate(ShopifyInfo $info): Model;

    public function find(string $domain): ?Model;
}
