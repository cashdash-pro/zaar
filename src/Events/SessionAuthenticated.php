<?php

namespace CashDash\Zaar\Events;

use CashDash\Zaar\Dtos\SessionData;

class SessionAuthenticated
{
    public function __construct(public SessionData $session) {}
}
