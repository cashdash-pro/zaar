<?php

namespace CashDash\Zaar\Actions\TokenExchangeAuth;

use CashDash\Zaar\Concerns\Actions\AsObject;
use CashDash\Zaar\Dtos\EmbeddedAuthData;
use Illuminate\Http\Request;

class DiscoverEmbeddedAuth
{
    use AsObject;

    public function handle(Request $request): ?EmbeddedAuthData
    {
        if (! $bearer_token = GetTokenFromRequest::make()->handle($request)) {
            return null;
        }

        $sessionToken = DecodeSessionToken::make()->handle($bearer_token);

        if (! $sessionToken) {
            return null;
        }

        $data = new EmbeddedAuthData($bearer_token, $sessionToken);

        app()->instance(EmbeddedAuthData::class, $data);

        return $data;
    }
}
