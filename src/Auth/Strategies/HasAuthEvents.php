<?php

namespace CashDash\Zaar\Auth\Strategies;

use CashDash\Zaar\Contracts\AuthFlow;
use CashDash\Zaar\Dtos\OfflineSessionData;
use CashDash\Zaar\Dtos\OnlineSessionData;
use CashDash\Zaar\Dtos\SessionData;
use CashDash\Zaar\Events\OfflineSessionLoaded;
use CashDash\Zaar\Events\OnlineSessionLoaded;
use CashDash\Zaar\Events\SessionAuthenticated;
use CashDash\Zaar\Events\ShopifyTenantLoaded;
use Illuminate\Database\Eloquent\Model;

trait HasAuthEvents
{
    private ?OnlineSessionData $onlineSession = null;

    private ?OfflineSessionData $offlineSession = null;

    private ?Model $shopify = null;

    private ?SessionData $sessionData = null;

    private ?string $domain = null;

    public function bindData(): AuthFlow
    {
        app()->instance(OnlineSessionData::class, $this->onlineSession);
        app()->instance(OfflineSessionData::class, $this->offlineSession);
        app()->instance(SessionData::class, $this->sessionData);

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
        if ($this->sessionData) {
            event(new SessionAuthenticated($this->sessionData));
        }

        return $this;
    }
}
