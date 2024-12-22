<?php

namespace CashDash\Zaar\Auth\Strategies;

use CashDash\Zaar\Actions\Creation\ShopifyCreation;
use CashDash\Zaar\Actions\Creation\ShopifyOfflineSessionCreation;
use CashDash\Zaar\Actions\Creation\ShopifyOnlineSessionCreation;
use CashDash\Zaar\Actions\Creation\UserCreation;
use CashDash\Zaar\Contracts\AuthFlow;
use CashDash\Zaar\Contracts\ShopifyRepositoryInterface;
use CashDash\Zaar\Contracts\ShopifySessionsRepositoryInterface;
use CashDash\Zaar\Contracts\UserRepositoryInterface;
use CashDash\Zaar\Dtos\EmbeddedAuthData;
use CashDash\Zaar\Dtos\SessionData;
use CashDash\Zaar\Exceptions\ShopifySessionNotStartedException;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;
use Illuminate\Support\Traits\Conditionable;

class EmbeddedStrategy implements AuthFlow
{
    use Conditionable;
    use HasAuthEvents;

    private ?Authenticatable $user = null;

    private ?EmbeddedAuthData $auth = null;

    public function __construct(
        private Request $request,
        private ShopifySessionsRepositoryInterface $sessionsRepository,
        private ShopifyRepositoryInterface $shopifyRepository,
        private UserRepositoryInterface $userRepository
    ) {
        if (app()->has(EmbeddedAuthData::class)) {
            $this->auth = app(EmbeddedAuthData::class);
        }
    }

    public function withOnlineSession(?Authenticatable $user): AuthFlow
    {
        if (! $this->auth) {
            return $this;
        }

        $this->onlineSession = $this->sessionsRepository->findOnline($this->auth->session_token->sid);
        if (! $this->onlineSession) {
            $this->onlineSession = ShopifyOnlineSessionCreation::make()->handle($this->auth);
        }

        return $this;
    }

    public function withUser(): AuthFlow
    {
        if (! $this->onlineSession) {
            return $this;
        }

        $user = $this->userRepository->find($this->onlineSession->user_id);
        if (! $user) {
            $user = UserCreation::make()->handle($this->onlineSession);
        }
        $this->user = $user;

        return $this;
    }

    public function withDomain(): AuthFlow
    {
        if ($this->auth) {
            $this->domain = $this->auth->session_token->dest;
        }

        if ($domain = $this->resolveDomainUsingCallback($this->auth->session_token->dest)) {
            $this->domain = $domain;
        }

        return $this;
    }

    public function withOfflineSession(): AuthFlow
    {
        if (! $this->auth) {
            return $this;
        }

        $authOfflineSession = $this->sessionsRepository->findOffline($this->auth->session_token->dest);
        if (! $authOfflineSession) {
            $authOfflineSession = ShopifyOfflineSessionCreation::make()->handle($this->auth);
        }

        if ($this->domain === $this->auth->session_token->dest) {
            $this->offlineSession = $authOfflineSession;
        } else {
            $this->offlineSession = $this->sessionsRepository->findOffline($this->domain);
        }

        return $this;
    }

    public function mergeSessions(): AuthFlow
    {
        try {
            $this->sessionData = SessionData::merge($this->onlineSession, $this->offlineSession);
        } catch (ShopifySessionNotStartedException) {
            $this->sessionData = null;
        }

        return $this;
    }

    public function withShopifyModel(): AuthFlow
    {
        if (! $this->sessionData) {
            return $this;
        }

        $shopify = $this->shopifyRepository->find($this->domain);

        // I'm not sure if this is a great idea considering the session could be an impersonation
        if (! $shopify) {
            $shopify = ShopifyCreation::make()->handle($this->sessionData);
        }
        $this->shopify = $shopify;

        return $this;
    }

    public function getUser(): ?Authenticatable
    {
        return $this->user;
    }

    public function getDomain(): ?string
    {
        return $this->domain;
    }
}
