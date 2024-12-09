<?php

namespace CashDash\Zaar\Dtos;

class EmbeddedAuthData
{
    public function __construct(
        public string $bearer_token,
        public SessionToken $session_token,
    ) {}
}
