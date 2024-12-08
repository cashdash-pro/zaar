<?php

namespace CashDash\Zaar\Events;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ShopifyUserCreated
{
    use Dispatchable, SerializesModels;

    public function __construct(Authenticatable $user) {}
}
