<?php

namespace CashDash\Zaar\Dtos;

class PublicSessionToken
{
    public function __construct(
        /** @var string The shop's domain (e.g., "my-shop.myshopify.com") */
        public string $dest,

        /** @var string The client ID of the receiving app (e.g., "8273642...") */
        public string $aud,

        /** @var int When the session token expires (Unix timestamp e.g., 1684167642) */
        public int $exp,

        /** @var int When the session token activates (Unix timestamp e.g., 1684164042) */
        public int $nbf,

        /** @var int When the session token was issued (Unix timestamp e.g., 1684164042) */
        public int $iat,

        /** @var string A secure random UUID (e.g., "705dc341-b6c7-4e59-969d-4c699d78d375") */
        public string $jti,
    ) {}

    /**
     * Parse a JWT token payload into a SessionToken instance
     *
     * @param  \stdClass  $token  The decoded JWT payload
     */
    public static function parseToken(\stdClass $token): self
    {
        $dest = preg_replace('/^https?:\/\//', '', $token->dest);

        return new self(
            $dest,
            $token->aud,
            $token->exp,
            $token->nbf,
            $token->iat,
            $token->jti,
        );
    }
}
