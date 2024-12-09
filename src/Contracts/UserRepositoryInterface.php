<?php

namespace CashDash\Zaar\Contracts;

use CashDash\Zaar\Dtos\OnlineSessionData;
use Illuminate\Database\Eloquent\Model;

interface UserRepositoryInterface
{
    public function find(string|int $user_id): ?Model;

    public function create(OnlineSessionData $onlineSessionData);
}
