<?php

namespace CashDash\Zaar\Events;

use CashDash\Zaar\Dtos\SessionData;
use Illuminate\Database\Eloquent\Model;

class SessionAuthenticated
{
    public function __construct(public SessionData $session, Model $shopify, ?Model $user) {}
}
