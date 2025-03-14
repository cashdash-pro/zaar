<?php

namespace CashDash\Zaar\Actions\TokenExchangeAuth;

use CashDash\Zaar\Concerns\Actions\AsFake;
use CashDash\Zaar\Concerns\Actions\AsObject;
use CashDash\Zaar\Dtos\PublicSessionToken;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\SignatureInvalidException;

readonly class DecodeExtensionSessionToken
{
    use AsFake;
    use AsObject;

    public function handle(string $bearer_token): ?PublicSessionToken
    {
        $debugExceptions = [
            SignatureInvalidException::class,
        ];

        $secret = config('zaar.shopify_app.client_secret');
        if (! $secret) {
            throw new \Exception('Zaar Shopify Client Secret is not set');
        }

        try {
            JWT::$leeway = 60;
            $payload = JWT::decode(
                $bearer_token,
                new Key($secret, 'HS256')
            );

            $token = PublicSessionToken::parseToken($payload);

            app()->instance(PublicSessionToken::class, $token);

            return $token;
        } catch (\Throwable $exception) {
            if (in_array(get_class($exception), $debugExceptions) && ! app()->isProduction()) {
                throw $exception;
            }

            return null;
        }
    }
}
