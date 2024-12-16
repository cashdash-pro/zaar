<?php

namespace CashDash\Zaar\Actions\TokenExchangeAuth;

use CashDash\Zaar\Actions\Creation\ShopifyOnlineSessionCreation;
use CashDash\Zaar\Actions\Resolvers\ResolveOnlineSession;
use CashDash\Zaar\Concerns\Actions\AsFake;
use CashDash\Zaar\Concerns\Actions\AsObject;
use CashDash\Zaar\Contracts\ShopifySessionsRepositoryInterface;
use CashDash\Zaar\Dtos\EmbeddedAuthData;
use CashDash\Zaar\Dtos\OnlineSessionData;
use CashDash\Zaar\Events\OnlineSessionLoaded;
use Illuminate\Contracts\Auth\Authenticatable;

readonly class LoadOnlineSession
{
    use AsFake;
    use AsObject;

    public function __construct(
        private ShopifySessionsRepositoryInterface $sessionsRepository
    ) {}

    public function handle(?EmbeddedAuthData $auth, ?Authenticatable $user): ?OnlineSessionData
    {
        return \DB::transaction(function () use ($auth, $user) {

            if ($auth) {
                $session = $this->loadEmbeddedSession($auth);
            } else {
                $session = ResolveOnlineSession::make()->handle($user);
                if (! $session) {
                    return null;
                }
            }

            app()->instance(OnlineSessionData::class, $session);

            event(new OnlineSessionLoaded($session));

            return $session;
        });
    }

    private function loadEmbeddedSession(EmbeddedAuthData $auth): OnlineSessionData
    {
        $session = $this->sessionsRepository->findOnline($auth->session_token->sid);

        if (! $session) {
            $session = ShopifyOnlineSessionCreation::make()->handle($auth);
        }

        return $session;
    }
}
