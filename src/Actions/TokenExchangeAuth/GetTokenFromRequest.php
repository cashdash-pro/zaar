<?php

namespace CashDash\Zaar\Actions\TokenExchangeAuth;

use CashDash\Zaar\Concerns\Actions\AsObject;

class GetTokenFromRequest
{
    use AsObject;

    public function handle($request)
    {
        $auth = $request->header('Authorization', '');
        $parts = explode(' ', $auth);

        if ($token = $parts[1] ?? null) {
            return $token;
        }

        return $request->query('id_token');
    }
}
