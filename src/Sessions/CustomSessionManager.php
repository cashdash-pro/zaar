<?php

namespace CashDash\Zaar\Sessions;

use Illuminate\Session\SessionManager;

class CustomSessionManager extends SessionManager
{
    protected function buildSession($handler)
    {
        return $this->config->get('session.encrypt')
            ? $this->buildEncryptedSession($handler)
            : new CustomStore(
                $this->config->get('session.cookie'),
                $handler,
                $id = null,
                $this->config->get('session.serialization', 'php')
            );
    }

    protected function buildEncryptedSession($handler)
    {
        return new CustomEncryptedStore(
            $this->config->get('session.cookie'),
            $handler,
            $this->container['encrypter'],
            $id = null,
            $this->config->get('session.serialization', 'php'),
        );
    }
}
