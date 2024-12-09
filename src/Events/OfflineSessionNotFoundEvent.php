<?php

namespace CashDash\Zaar\Events;

use Illuminate\Foundation\Events\Dispatchable;

class OfflineSessionNotFoundEvent
{
    use Dispatchable;

    public function __construct()
    {
    }
}
