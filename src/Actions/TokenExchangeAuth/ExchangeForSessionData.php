<?php

namespace CashDash\Zaar\Actions\TokenExchangeAuth;

use Carbon\CarbonImmutable;
use CashDash\Zaar\Concerns\Actions\AsFake;
use CashDash\Zaar\Concerns\Actions\AsObject;
use CashDash\Zaar\Dtos\OfflineSessionData;
use CashDash\Zaar\Dtos\OnlineSessionData;
use CashDash\Zaar\Dtos\SessionToken;
use Illuminate\Support\Facades\Http;

class ExchangeForSessionData
{
    use AsFake;
    use AsObject;

    public function post(string $bearer_token, SessionToken $token, bool $isOnline): mixed
    {
        $secret = config('zaar.shopify_app.client_secret');
        if (! $secret) {
            throw new \Exception('Zaar Shopify Client Secret is not set');
        }

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ])->retry(3)
            ->post($token->adminDomain().'/oauth/access_token', [
                'client_id' => config('zaar.shopify_app.client_id'),
                'client_secret' =>$secret,
                'grant_type' => 'urn:ietf:params:oauth:grant-type:token-exchange',
                'subject_token' => $bearer_token,
                'subject_token_type' => 'urn:ietf:params:oauth:token-type:id_token',
                'requested_token_type' => 'urn:shopify:params:oauth:token-type:'.($isOnline ? 'online' : 'offline').'-access-token',
            ]);

        abort_if($response->status() !== 200, 401, 'Failed to fetch access token');

        return $response->json();
    }

    public function handleOnline(string $bearer_token, SessionToken $sessionToken): OnlineSessionData
    {
        $json = $this->post($bearer_token, $sessionToken, true);

        return new OnlineSessionData(
            id: $sessionToken->sid,
            shop: $sessionToken->dest,
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

    public function handleOffline(string $bearer_token, SessionToken $sessionToken): OfflineSessionData
    {
        $json = $this->post($bearer_token, $sessionToken, false);

        return new OfflineSessionData(
            id: $sessionToken->dest.'_offline',
            shop: $sessionToken->dest,
            state: 'token_exchange',
            is_online: false,
            scope: $json['scope'] ?? null,
            expires_at: null,
            access_token: $json['access_token'],
        );
    }
}
