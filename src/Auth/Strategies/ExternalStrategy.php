<?php

namespace CashDash\Zaar\Auth\Strategies;

use CashDash\Zaar\Contracts\AuthFlow;
use CashDash\Zaar\Contracts\ProvidesShopifySessions;
use CashDash\Zaar\Contracts\ShopifyRepositoryInterface;
use CashDash\Zaar\Contracts\ShopifySessionsRepositoryInterface;
use CashDash\Zaar\Contracts\UserRepositoryInterface;
use CashDash\Zaar\Dtos\SessionData;
use CashDash\Zaar\Zaar;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;
use Illuminate\Support\Traits\Conditionable;

class ExternalStrategy implements AuthFlow
{
    use Conditionable;
    use HasAuthEvents;

    private ?Authenticatable $user = null;

    public const SESSION_DOMAIN = 'auth_domain';

    public function __construct(
        private Request $request,
        private ShopifySessionsRepositoryInterface $sessionsRepository,
        private ShopifyRepositoryInterface $shopifyRepository,
        private UserRepositoryInterface $userRepository
    ) {}

    public function withOnlineSession(Request $request, ?Authenticatable $user): AuthFlow
    {
        if (! $user) {
            return $this;
        }

        if (! $user instanceof ProvidesShopifySessions) {
            throw new \InvalidArgumentException('The user model must implement ProvidesShopifySessions and use the HasOnlineSessions trait.');
        }

        $this->user = $user;
        $this->onlineSession = $user->onlineSession();

        return $this;
    }

    public function withUser(): AuthFlow
    {
        return $this;
    }

    public function withDomain(): AuthFlow
    {
        if (! $callback = Zaar::$resolveExternalRequest) {
            $this->resolveUsingSession();

            return $this;
        }

        $this->domain = $callback($this->request);
        if ($this->domain) {
            // append .myshopify.com if it's not there
            if (! str_contains($this->domain, '.')) {
                $this->domain .= '.myshopify.com';
            }
        }

        if (! $this->domain) {
            // attempt to restore from session
            $this->resolveUsingSession();
        }

        return $this;
    }

    private function resolveUsingSession(): void
    {
        $this->domain = $this->request->session()->get(self::SESSION_DOMAIN);
    }

    public function withOfflineSession(): AuthFlow
    {
        if (! $this->domain) {
            return $this;
        }
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
        if (! $this->domain) {
            return $this;
        }

        $this->shopify = $this->shopifyRepository->find($this->domain);

        return $this;
    }

    public function getUser(): ?Authenticatable
    {
        //        dd($this);
        return $this->user;
    }

    public function getDomain(): ?string
    {
        return $this->domain;
    }
}
