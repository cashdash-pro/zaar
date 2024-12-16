<?php

namespace CashDash\Zaar\Actions\TokenExchangeAuth;

use CashDash\Zaar\Actions\Resolvers\ResolveOfflineSession;
use CashDash\Zaar\Concerns\Actions\AsFake;
use CashDash\Zaar\Concerns\Actions\AsObject;
use CashDash\Zaar\Contracts\ShopifySessionsRepositoryInterface;
use CashDash\Zaar\Dtos\EmbeddedAuthData;
use CashDash\Zaar\Dtos\OfflineSessionData;
use CashDash\Zaar\Events\OfflineSessionLoaded;
use CashDash\Zaar\Exceptions\OfflineSessionNotFoundException;

readonly class LoadOfflineSession
{
    use AsFake;
    use AsObject;

    public function __construct(
        private ShopifySessionsRepositoryInterface $repository
    ) {}

    /**
     * @throws OfflineSessionNotFoundException
     */
    public function handle(string $domain, ?EmbeddedAuthData $auth): OfflineSessionData
    {
        \DB::beginTransaction();

        try {
            $session = $this->repository->findOffline($domain);
            if (! $session) {
                $session = ResolveOfflineSession::make()->handle($auth);
            }
            \DB::commit();

            app()->instance(OfflineSessionData::class, $session);

            event(new OfflineSessionLoaded($session));

            return $session;
        } catch (\OfflineSessionNotFoundException $e) {
            \DB::rollBack();
            throw $e;
        }
        \DB::rollBack();
    }
}
