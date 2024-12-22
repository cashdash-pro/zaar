<?php

namespace CashDash\Zaar\Contracts;

use CashDash\Zaar\Dtos\OnlineSessionData;

interface ProvidesOnlineSessions
{
    public function onlineSessions(): ?OnlineSessionData;
}
