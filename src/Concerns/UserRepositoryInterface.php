<?php

namespace CashDash\Zaar\Concerns;

use CashDash\Zaar\Dtos\OnlineSessionData;
use Illuminate\Database\Eloquent\Model;

interface UserRepositoryInterface
{
    public function find(OnlineSessionData $session): ?Model;

    public function create(OnlineSessionData $onlineSessionData);
}
