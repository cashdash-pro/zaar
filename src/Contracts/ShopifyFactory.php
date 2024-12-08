<?php

namespace CashDash\Zaar\Contracts;

use CashDash\Zaar\Dtos\SessionData;
use Illuminate\Database\Eloquent\Model;

interface ShopifyFactory
{
    public function createFromSession(SessionData $session): Model;
}
