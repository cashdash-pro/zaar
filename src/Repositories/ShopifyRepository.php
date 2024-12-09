<?php

namespace CashDash\Zaar\Repositories;

use CashDash\Zaar\Contracts\ShopifyRepositoryInterface;
use CashDash\Zaar\Dtos\ShopifyInfo;
use CashDash\Zaar\Models\Shopify;
use Illuminate\Database\Eloquent\Model;

class ShopifyRepository implements ShopifyRepositoryInterface
{
    /**
     * @return class-string<\Illuminate\Database\Eloquent\Model>
     */
    private function model(): string
    {
        return config('zaar.repositories.shopify.model', Shopify::class);
    }

    public function updateOrCreate(ShopifyInfo $info): Model
    {
        return $this->model()::updateOrCreate([
            'domain' => $info->domain,
        ], [
            'shopify_id' => $info->id,
            'name' => $info->name,
            'primary_domain' => $info->primaryDomain,
            'contact_email' => $info->contactEmail,
            'email' => $info->email,
            'description' => $info->description,
            'currency_code' => $info->currencyCode,
            'money_format' => $info->moneyFormat,
            'money_with_currency_format' => $info->moneyWithCurrencyFormat,
            'iana_timezone' => $info->ianaTimezone,
            'plan_name' => $info->plan_name,
            'shopify_plus' => $info->shopifyPlus,
        ]);
    }

    public function find(string $domain): ?Model
    {
        return $this->model()::query()
            ->where('domain', $domain)
            ->first();
    }
}
