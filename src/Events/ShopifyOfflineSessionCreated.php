<?php

namespace CashDash\Zaar\Events;

use CashDash\Zaar\Dtos\OfflineSessionData;
use Illuminate\Foundation\Events\Dispatchable;

class ShopifyOfflineSessionCreated
{
    use Dispatchable;

    public function __construct(public OfflineSessionData $session) {}
}
