<?php

namespace CashDash\Zaar\Actions\TokenExchangeAuth;

use CashDash\Zaar\Actions\User\ShopifyOnlineSessionCreation;
use CashDash\Zaar\Concerns\Actions\AsFake;
use CashDash\Zaar\Concerns\Actions\AsObject;
use CashDash\Zaar\Concerns\ShopifySessionsRepositoryInterface;
use CashDash\Zaar\Dtos\OnlineSessionData;
use CashDash\Zaar\Dtos\SessionToken;
use CashDash\Zaar\Events\OnlineSessionLoaded;

readonly class LoadOnlineSession
{
    use AsFake;
    use AsObject;

    public function __construct(
        private ShopifySessionsRepositoryInterface $sessionsRepository
    ) {}

    public function handle(string $bearer_token, SessionToken $sessionToken): OnlineSessionData
    {
        return \DB::transaction(function () use ($bearer_token, $sessionToken) {

            $session = $this->sessionsRepository->findOnline($sessionToken->sid);

            if (! $session) {
                $session = ShopifyOnlineSessionCreation::make()->handle($bearer_token, $sessionToken);
            }

            app()->instance(OnlineSessionData::class, $session);

            event(new OnlineSessionLoaded($session));

            return $session;
        });
    }
}
