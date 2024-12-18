<?php

namespace CashDash\Zaar\Auth\Strategies;

use CashDash\Zaar\Contracts\AuthFlow;
use CashDash\Zaar\Contracts\ShopifyRepositoryInterface;
use CashDash\Zaar\Contracts\ShopifySessionsRepositoryInterface;
use CashDash\Zaar\Contracts\UserRepositoryInterface;
use CashDash\Zaar\Dtos\PublicSessionToken;
use CashDash\Zaar\Dtos\SessionData;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;
use Illuminate\Support\Traits\Conditionable;
use Webmozart\Assert\Assert;

class PublicStrategy implements AuthFlow
{
    use Conditionable;
    use HasAuthEvents;

    private ?Authenticatable $user = null;

    public const SESSION_DOMAIN = 'auth_domain';

    public function __construct(
        private Request $request,
        private PublicSessionToken $token,
        private ShopifySessionsRepositoryInterface $sessionsRepository,
        private ShopifyRepositoryInterface $shopifyRepository,
        private UserRepositoryInterface $userRepository
    ) {}

    public function withOnlineSession(?Authenticatable $user): AuthFlow
    {
        return $this;
    }

    public function withUser(): AuthFlow
    {
        return $this;
    }

    public function withDomain(): AuthFlow
    {
        $this->domain = $this->token->dest;

        return $this;
    }

    public function withOfflineSession(): AuthFlow
    {
        Assert::notNull($this->domain);

        $this->offlineSession = $this->sessionsRepository->findOffline($this->domain);

        return $this;
    }

    public function mergeSessions(): AuthFlow
    {
        if (! $this->onlineSession && ! $this->offlineSession) {
            return $this;
        }

        $this->sessionData = SessionData::merge($this->onlineSession, $this->offlineSession);

        return $this;
    }

    public function withShopifyModel(): AuthFlow
    {
        if (! $this->sessionData) {
            return $this;
        }

        $this->shopify = $this->shopifyRepository->find($this->sessionData->shop);

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
