<?php

namespace CashDash\Zaar\Actions\TokenExchangeAuth;

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
                'client_secret' => $secret,
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

        return OnlineSessionData::fromTokenResponse($sessionToken->sid, $sessionToken->dest, $json);
    }

    public function handleOffline(string $bearer_token, SessionToken $sessionToken): OfflineSessionData
    {
        $json = $this->post($bearer_token, $sessionToken, false);

        return OfflineSessionData::fromTokenResponse($domain, $json);

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
