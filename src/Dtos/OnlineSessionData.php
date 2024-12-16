<?php

namespace CashDash\Zaar\Dtos;

use Carbon\CarbonImmutable;
use CashDash\Zaar\Concerns\Actions\AsFake;
use CashDash\Zaar\Concerns\Actions\AsObject;
use CashDash\Zaar\Models\ShopifySession;

class OnlineSessionData
{
    use AsFake;
    use AsObject;

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

    public static function fromModel(ShopifySession $model): OnlineSessionData
    {
        return new self(
            id: $model->id,
            shop: $model->shop,
            state: $model->state,
            is_online: $model->is_online,
            scope: $model->scope,
            expires_at: $model->expires_at,
            access_token: $model->access_token,
            user_id: $model->user_id,
            first_name: $model->first_name,
            last_name: $model->last_name,
            email: $model->email,
            email_verified: $model->email_verified,
            account_owner: $model->account_owner,
            locale: $model->locale,
            collaborator: $model->collaborator,
            user_scopes: $model->user_scopes
        );
    }

    public static function fromTokenResponse(string $sessionId, string $domain, array $json): OnlineSessionData
    {
        return new OnlineSessionData(
            id: $sessionId,
            shop: $domain,
            state: 'token_exchange',
            is_online: true,
            scope: $json['scope'] ?? null,
            expires_at: $json['expires_in'] ? CarbonImmutable::now()->addSeconds($json['expires_in']) : null,
            access_token: $json['access_token'],
            user_id: $json['associated_user']['id'],
            first_name: $json['associated_user']['first_name'],
            last_name: $json['associated_user']['last_name'],
            email: $json['associated_user']['email'],
            email_verified: $json['associated_user']['email_verified'],
            account_owner: $json['associated_user']['account_owner'],
            locale: $json['associated_user']['locale'],
            collaborator: $json['associated_user']['collaborator'],
            user_scopes: $json['associated_user_scope'],
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'shop' => $this->shop,
            'state' => $this->state,
            'is_online' => $this->is_online,
            'scope' => $this->scope,
            'expires_at' => $this->expires_at,
            'access_token' => $this->access_token,
            'user_id' => $this->user_id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'email_verified' => $this->email_verified,
            'account_owner' => $this->account_owner,
            'locale' => $this->locale,
            'collaborator' => $this->collaborator,
            'user_scopes' => $this->user_scopes,
        ];
    }
}
