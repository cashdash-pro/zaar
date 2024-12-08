<?php

namespace CashDash\Zaar\Dtos;

use Carbon\CarbonImmutable;
use CashDash\Zaar\Concerns\Actions\AsFake;

class SessionData
{
    use AsFake;

    public function __construct(
        public string $id,
        public string $shop,
        public string $state,
        public bool $is_online,
        public ?string $scope,
        public ?CarbonImmutable $expires_at,
        #[\SensitiveParameter]
        public ?string $access_token,

        public int $user_id,
        public string $first_name,
        public string $last_name,
        public string $email,
        public bool $email_verified,
        public bool $account_owner,
        public string $locale,
        public bool $collaborator,
        public string $user_scopes,
    ) {}

    public static function merge(OnlineSessionData $online, OfflineSessionData $offline): SessionData
    {
        return new self(
            id: $offline->id,
            shop: $offline->shop,
            state: $offline->state,
            is_online: false,
            scope: $offline->scope,
            expires_at: $offline->expires_at,
            access_token: $offline->access_token,

            user_id: $online->user_id,
            first_name: $online->first_name,
            last_name: $online->last_name,
            email: $online->email,
            email_verified: $online->email_verified,
            account_owner: $online->account_owner,
            locale: $online->locale,
            collaborator: $online->collaborator,
            user_scopes: $online->user_scopes,
        );
    }
}
