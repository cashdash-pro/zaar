<?php

namespace CashDash\Zaar\Dtos;

class ShopifyInfo
{
    public function __construct(
        public string $id,
        public string $name,
        public string $domain,
        public string $primaryDomain,
        public string $contactEmail,
        public string $email,
        public ?string $description,
        public string $currencyCode,
        public string $moneyFormat,
        public string $moneyWithCurrencyFormat,
        public string $ianaTimezone,
        public string $plan_name,
        public bool $shopifyPlus,
    ) {}
}
