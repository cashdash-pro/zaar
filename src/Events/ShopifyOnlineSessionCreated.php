<?php

namespace CashDash\Zaar\Events;

use CashDash\Zaar\Dtos\OnlineSessionData;
use Illuminate\Foundation\Events\Dispatchable;

class ShopifyOnlineSessionCreated
{
    use Dispatchable;

    public function __construct(
        public OnlineSessionData $session
    ) {}
}
