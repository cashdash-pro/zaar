<?php

namespace CashDash\Zaar\Contracts;

use CashDash\Zaar\Dtos\OfflineSessionData;

interface ProvidesOfflineSession
{
    public function offlineSession(): ?OfflineSessionData;
}
