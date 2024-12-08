<?php

namespace CashDash\Zaar\Events;

use CashDash\Zaar\Dtos\OfflineSessionData;

class OfflineSessionLoaded
{
    public function __construct(public OfflineSessionData $session) {}
}
