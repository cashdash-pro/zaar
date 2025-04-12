<?php

namespace CashDash\Zaar\Auth\Strategies;

use CashDash\Zaar\Contracts\AuthFlow;
use CashDash\Zaar\Contracts\ProvidesOnlineSessions;
use CashDash\Zaar\Contracts\ShopifyRepositoryInterface;
use CashDash\Zaar\Contracts\ShopifySessionsRepositoryInterface;
use CashDash\Zaar\Contracts\UserRepositoryInterface;
use CashDash\Zaar\Dtos\OfflineSessionData;
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
        private readonly Request $request,
        private readonly ShopifySessionsRepositoryInterface $sessionsRepository,
        private readonly ShopifyRepositoryInterface $shopifyRepository,
        private readonly UserRepositoryInterface $userRepository
    ) {}

    public function withOnlineSession(?Authenticatable $user): AuthFlow
    {
        if (! $user) {
            return $this;
        }

        if (! $user instanceof ProvidesOnlineSessions) {
            throw new \InvalidArgumentException('The user model must implement ProvidesShopifySessions and use the HasOnlineSessions trait.');
        }

        $this->user = $user;
        $this->onlineSession = $user->onlineSession();

        if (! $this->onlineSession) {
            // potentially redirect if there's no online session

        }

        return $this;
    }

    public function withUser(?Authenticatable $user): AuthFlow
    {
        if ($user) {
            $this->user = $user;

            return $this;
        }

        return $this;
    }

    public function withDomain(): AuthFlow
    {
        $this->domain = $this->resolveUsingSession();

        if ($domain = $this->resolveDomainUsingCallback($this->domain)) {
            $this->domain = $domain;
        }

        return $this;
    }

    private function resolveUsingSession(): ?string
    {
        return $this->request->session()->get(self::SESSION_DOMAIN);
    }

    public function withOfflineSession(): AuthFlow
    {
        if (! $this->domain) {
            return $this;
        }

        if (app()->has(OfflineSessionData::class) && $offlineSession = app(OfflineSessionData::class)) {
            if ($offlineSession->shop === $this->domain) {
                $this->offlineSession = $offlineSession;

                return $this;
            }
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
        if (! $this->sessionData) {
            return $this;
        }

        if (app()->has('zaar.shopify') && $shopify = app('zaar.shopify')) {
            if ($shopify->shop === $this->sessionData->shop) {
                $this->shopify = $shopify;

                return $this;
            }
        }

        if ($shopifyCallback = Zaar::$shopifyTenant) {
            $shopify = $shopifyCallback();
            if ($shopify instanceof \Illuminate\Database\Eloquent\Model && $shopify->{config('zaar.repositories.shopify.shop_domain_column')} === $this->sessionData->shop) {
                $this->shopify = $shopify;
                return $this;
            }
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
