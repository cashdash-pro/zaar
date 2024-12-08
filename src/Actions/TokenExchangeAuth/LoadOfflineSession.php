<?php

namespace CashDash\Zaar\Actions\TokenExchangeAuth;

use CashDash\Zaar\Actions\User\ShopifyOfflineSessionCreation;
use CashDash\Zaar\Concerns\Actions\AsFake;
use CashDash\Zaar\Concerns\Actions\AsObject;
use CashDash\Zaar\Concerns\ShopifySessionsRepositoryInterface;
use CashDash\Zaar\Dtos\OfflineSessionData;
use CashDash\Zaar\Dtos\SessionToken;
use CashDash\Zaar\Events\OfflineSessionCreated;
use CashDash\Zaar\Events\OfflineSessionLoaded;

readonly class LoadOfflineSession
{
    use AsFake;
    use AsObject;

    public function __construct(
        private ShopifySessionsRepositoryInterface $repository
    ) {}

    public function handle(string $bearer_token, SessionToken $sessionToken): OfflineSessionData
    {
        return \DB::transaction(function () use ($bearer_token, $sessionToken) {

            $session = $this->repository->findOffline($sessionToken->dest);

            if (! $session) {
                $session = ShopifyOfflineSessionCreation::make()->handle($bearer_token, $sessionToken);
                event(new OfflineSessionCreated($session));
            }

            app()->instance(OfflineSessionData::class, $session);

            event(new OfflineSessionLoaded($session));

            return $session;
        });
    }
}
