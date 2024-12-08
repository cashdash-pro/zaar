<?php

namespace CashDash\Zaar\Sessions;

use CashDash\Zaar\Actions\TokenExchangeAuth\DecodeSessionToken;
use CashDash\Zaar\Actions\TokenExchangeAuth\GetTokenFromRequest;
use Illuminate\Session\EncryptedStore;

class CustomEncryptedStore extends EncryptedStore
{
    public function getId()
    {
        $bearer_token = GetTokenFromRequest::make()->handle(request());
        if ($bearer_token && $session = DecodeSessionToken::make()->handle($bearer_token)) {
            return sha1($session->sid);
        }

        return parent::getId();
    }
}
