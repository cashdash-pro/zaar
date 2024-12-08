<?php

namespace CashDash\Zaar\Dtos;

class SessionToken
{
    public function __construct(
        /** @var string The shop's admin domain (e.g., "my-shop.myshopify.com/admin") */
        public string $iss,

        /** @var string The shop's domain (e.g., "my-shop.myshopify.com") */
        public string $dest,

        /** @var string The client ID of the receiving app (e.g., "8273642") */
        public string $aud,

        /** @var string The User ID that the session token is intended for (e.g., "4252342") */
        public string $sub,

        /** @var int When the session token expires (Unix timestamp e.g., 1684167642) */
        public int $exp,

        /** @var int When the session token activates (Unix timestamp e.g., 1684164042) */
        public int $nbf,

        /** @var int When the session token was issued (Unix timestamp e.g., 1684164042) */
        public int $iat,

        /** @var string A secure random UUID (e.g., "705dc341-b6c7-4e59-969d-4c699d78d375") */
        public string $jti,

        /** @var string A unique session ID per user and app (e.g., "cs_123456789") */
        public string $sid,

        /** @var string Shopify signature (e.g., "a1b2c3d4e5f6...") */
        public string $sig,
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
            $token->iss,
            $dest,
            $token->aud,
            $token->sub,
            $token->exp,
            $token->nbf,
            $token->iat,
            $token->jti,
            $token->sid,
            $token->sig
        );
    }

    public function adminDomain(): string
    {
        return $this->iss;
    }
}
