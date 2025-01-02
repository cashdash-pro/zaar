<?php

namespace CashDash\Zaar\Repositories;

use CashDash\Zaar\Contracts\UserRepositoryInterface;
use CashDash\Zaar\Dtos\OnlineSessionData;
use CashDash\Zaar\Zaar;
use Illuminate\Database\Eloquent\Model;

class UserRepository implements UserRepositoryInterface
{
    private function model(): string
    {
        return config('zaar.repositories.user.model');
    }

    private function emailColumn(): string
    {
        return config('zaar.repositories.user.email_column', 'email');
    }

    public function find(?string $email): ?Model
    {
        if (! $email) {
            return null;
        }

        return $this->model()::where($this->emailColumn(), $email)->first();
    }

    public function create(OnlineSessionData $onlineSessionData)
    {
        if ($callback = Zaar::$createUserCallback) {
            return $callback($onlineSessionData);
        }

        $user = $this->model()::firstOrCreate(
            [
                'email' => $onlineSessionData->email,
            ],
            [
                'name' => $onlineSessionData->first_name.' '.$onlineSessionData->last_name,
                'password' => null,
                config('zaar.user.shopify_user_id_column', 'shopify_user_id') => $onlineSessionData->user_id,
                'email_verified_at' => $onlineSessionData->email_verified ? now() : null,
            ]
        );

        return $user;
    }
}
