<?php

namespace CashDash\Zaar\Actions\User;

use CashDash\Zaar\Actions\Shopify\ShopifyGetInfo;
use CashDash\Zaar\Concerns\Actions\AsObject;
use CashDash\Zaar\Concerns\ShopifyRepositoryInterface;
use CashDash\Zaar\Dtos\SessionData;
use CashDash\Zaar\Events\ShopifyCreated;
use Illuminate\Database\Eloquent\Model;

class ShopifyCreation
{
    use AsObject;

    public function __construct(
        private ShopifyRepositoryInterface $repository
    ) {}

    public function handle(SessionData $data): ?Model
    {
        $info = ShopifyGetInfo::make()->handle($data);
        $shopify = $this->repository->updateOrCreate($info);

        event(new ShopifyCreated($shopify));

        return $shopify;
    }
}
