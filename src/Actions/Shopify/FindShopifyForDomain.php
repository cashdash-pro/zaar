<?php

namespace CashDash\Zaar\Actions\Shopify;

use CashDash\Zaar\Concerns\Actions\AsObject;
use CashDash\Zaar\Contracts\ProvidesShopify;
use CashDash\Zaar\Zaar;
use Illuminate\Database\Eloquent\Model;

class FindShopifyForDomain
{
    use AsObject;

    /**
     * @return null|Model<ProvidesShopify>
     */
    public function handle(string $domain): ?Model
    {
        $model = Zaar::$shopifyModel;

        return $model::findForShopifyDomain($domain);
    }
}
