<?php

namespace CashDash\Zaar\Actions\Creation;

use CashDash\Zaar\Concerns\Actions\AsObject;
use CashDash\Zaar\Contracts\UserRepositoryInterface;
use CashDash\Zaar\Dtos\OnlineSessionData;
use CashDash\Zaar\Events\ShopifyUserCreated;
use Illuminate\Contracts\Auth\Authenticatable;

class UserCreation
{
    use AsObject;

    public function __construct(
        private UserRepositoryInterface $userRepository,
    ) {}

    public function handle(OnlineSessionData $onlineSessionData): ?Authenticatable
    {
        $user = $this->userRepository->create($onlineSessionData);

        ShopifyUserCreated::dispatch($user);

        return $user;
    }
}
