<?php

namespace CashDash\Zaar\Contracts;

use CashDash\Zaar\Dtos\OnlineSessionData;

interface ProvidesShopifySessions
{
    public function onlineSession(): ?OnlineSessionData;
}
