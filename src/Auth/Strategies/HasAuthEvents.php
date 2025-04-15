<?php

namespace CashDash\Zaar\Auth\Strategies;

use CashDash\Zaar\Contracts\AuthFlow;
use CashDash\Zaar\Contracts\ShopifySessionsRepositoryInterface;
use CashDash\Zaar\Dtos\OfflineSessionData;
use CashDash\Zaar\Dtos\OnlineSessionData;
use CashDash\Zaar\Dtos\SessionData;
use CashDash\Zaar\Events\OfflineSessionLoaded;
use CashDash\Zaar\Events\OnlineSessionLoaded;
use CashDash\Zaar\Events\SessionAuthenticated;
use CashDash\Zaar\Events\ShopifyOfflineSessionCreated;
use CashDash\Zaar\Events\ShopifyOnlineSessionCreated;
use CashDash\Zaar\Events\ShopifyTenantLoaded;
use CashDash\Zaar\SessionType;
use CashDash\Zaar\Zaar;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

trait HasAuthEvents
{
    private bool $shouldStoreOnlineSession = false;
    private bool $shouldStoreOfflineSession = false;

    private ?OnlineSessionData $onlineSession = null;

    private ?OfflineSessionData $offlineSession = null;

    private ?Model $shopify = null;

    private ?SessionData $sessionData = null;

    private ?string $domain = null;

    public function run(?Authenticatable $user): ?Authenticatable
    {
        return $this
            ->withOnlineSession($user)
            ->withUser($user)
            ->withDomain()
            ->when(Zaar::sessionType() === SessionType::OFFLINE, fn (AuthFlow $auth) => $auth->withOfflineSession())
            ->mergeSessions()
            ->bindData()
            ->withShopifyModel()
            ->storeSessions()
            ->dispatchEvents()
            ->getUser();
    }

    public function bindData(): AuthFlow
    {
        app()->scoped(OnlineSessionData::class, fn () => $this->onlineSession);
        app()->scoped(OfflineSessionData::class, fn () => $this->offlineSession);
        app()->scoped(SessionData::class, fn () => $this->sessionData);
        app()->scoped('zaar.shopify', fn () => $this->shopify);

        return $this;
    }

    public function storeSessions(): AuthFlow
    {
        $repo = app(ShopifySessionsRepositoryInterface::class);
        if ($this->shouldStoreOnlineSession) {
            $repo->createOnline($this->onlineSession);
            event(new ShopifyOnlineSessionCreated($this->onlineSession));
        }
        if ($this->shouldStoreOfflineSession) {
            $repo->createOffline($this->offlineSession);
            event(new ShopifyOfflineSessionCreated($this->offlineSession));
        }

        return $this;
    }

    public function setSessionData(): AuthFlow
    {
        if (! $this->domain) {
            return $this;
        }
        $this->request->session()->put('auth_domain', $this->domain);

        return $this;
    }

    public function dispatchEvents(): AuthFlow
    {
        if ($this->onlineSession) {
            event(new OnlineSessionLoaded($this->onlineSession));
        }
        if ($this->offlineSession) {
            event(new OfflineSessionLoaded($this->offlineSession));
        }
        if ($this->shopify) {
            event(new ShopifyTenantLoaded($this->shopify));
        }
        if ($this->sessionData && $this->shopify) {
            event(new SessionAuthenticated($this->sessionData, $this->shopify, $this->user));
        }

        return $this;
    }

    protected function resolveDomainUsingCallback(?string $current_domain): ?string
    {
        if (! $callback = Zaar::$resolveExternalRequest) {
            return null;
        }

        $domain = $callback($this->request, $this->user, $current_domain);
        if ($domain) {
            // append .myshopify.com if it's not there
            if (! str_contains($domain, '.')) {
                $domain .= '.myshopify.com';
            }
        }

        return $domain;
    }
}
