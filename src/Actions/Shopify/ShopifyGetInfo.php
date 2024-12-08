<?php

namespace CashDash\Zaar\Actions\Shopify;

use CashDash\Zaar\Concerns\Actions\AsFake;
use CashDash\Zaar\Concerns\Actions\AsObject;
use CashDash\Zaar\Dtos\SessionData;
use CashDash\Zaar\Dtos\ShopifyInfo;

class ShopifyGetInfo
{
    use AsFake;
    use AsObject;

    public function handle(SessionData $sessionData): ShopifyInfo
    {
        $queryString = <<<'QUERY'
{
	shop {
		name
    contactEmail
    primaryDomain {
      url
    }
    plan {
      displayName
      shopifyPlus
    }
    id
    ianaTimezone
    email
    description
    currencyCode
    currencyFormats {
      moneyFormat
      moneyWithCurrencyFormat
    }
	}
}
QUERY;

        $response = \Http::withHeaders([
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'X-Shopify-Access-Token' => $sessionData->access_token,
        ])->retry(3)
            ->post('https://'.$sessionData->shop.'/admin/api/2024-10/graphql.json', [
                'query' => $queryString,
            ]);

        abort_if($response->status() !== 200, 401, 'Failed to fetch access token');

        $json = $response->json();

        $shop = $json['data']['shop'];

        return new ShopifyInfo(
            id: $shop['id'],
            name: $shop['name'],
            domain: $sessionData->shop,
            primaryDomain: $shop['primaryDomain']['url'],
            contactEmail: $shop['contactEmail'],
            email: $shop['email'],
            description: $shop['description'] ?? null,
            currencyCode: $shop['currencyCode'],
            moneyFormat: $shop['currencyFormats']['moneyFormat'],
            moneyWithCurrencyFormat: $shop['currencyFormats']['moneyWithCurrencyFormat'],
            ianaTimezone: $shop['ianaTimezone'],
            plan_name: $shop['plan']['displayName'],
            shopifyPlus: $shop['plan']['shopifyPlus'],
        );
    }
}
