<?php

namespace CashDash\Zaar\Dtos;

use CashDash\Zaar\Concerns\Actions\AsFake;

class SessionData
{
    use AsFake;

    public function __construct(
        public string $id,
        public string $shop,
        public bool $is_online,
        public string $scope,
        #[\SensitiveParameter]
        public string $access_token,
    ) {}

    public static function merge(?OnlineSessionData $online, ?OfflineSessionData $offline): ?SessionData
    {
        if ($online === null && $offline === null) {
            return null;
        }

        return new self(
            id: $offline?->id ?? $online?->id,
            shop: $offline?->shop ?? $online?->shop,
            is_online: $offline?->is_online ?? $online?->is_online,
            scope: $offline?->scope ?? $online?->scope,
            access_token: $offline?->access_token ?? $online?->access_token,
        );
    }
}
