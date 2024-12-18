<?php

namespace CashDash\Zaar\Dtos;

use Carbon\CarbonImmutable;
use CashDash\Zaar\Concerns\Actions\AsFake;
use CashDash\Zaar\Models\ShopifySession;

class OfflineSessionData
{
    use AsFake;

    // TODO: remove things that aren't needed

    public function __construct(
        public string $id,
        public string $shop,
        public bool $is_online,
        public ?string $scope,
        public ?CarbonImmutable $expires_at,
        #[\SensitiveParameter]
        public string $access_token,
    ) {}

    public static function fromModel(ShopifySession $model): OfflineSessionData
    {
        return new self(
            id: $model->id,
            shop: $model->shop,
            is_online: $model->is_online,
            scope: $model->scope,
            expires_at: $model->expires_at,
            access_token: $model->access_token,
        );
    }

    public static function fromTokenResponse($domain, mixed $json): OfflineSessionData
    {
        return new OfflineSessionData(
            id: $domain.'_offline',
            shop: $domain,
            is_online: false,
            scope: $json['scope'] ?? null,
            expires_at: null,
            access_token: $json['access_token'],
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'shop' => $this->shop,
            'is_online' => $this->is_online,
            'scope' => $this->scope,
            'expires_at' => $this->expires_at,
            'access_token' => $this->access_token,
        ];
    }
}
