<?php

namespace CashDash\Zaar\Events;

use CashDash\Zaar\Dtos\OnlineSessionData;

class OnlineSessionLoaded
{
    public function __construct(public OnlineSessionData $session) {}
}
