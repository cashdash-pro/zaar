<?php

namespace CashDash\Zaar\Contracts;

use Illuminate\Contracts\Auth\Authenticatable;

interface AuthFlow
{
    public function run(?Authenticatable $user): ?Authenticatable;

    public function withOnlineSession(?Authenticatable $user): self;

    public function withUser(?Authenticatable $user): self;

    public function withDomain(): self;

    public function setSessionData(): self;

    public function withOfflineSession(): self;

    public function mergeSessions(): self;

    public function withShopifyModel(): self;

    public function bindData(): self;

    public function dispatchEvents(): self;

    public function getUser(): ?Authenticatable;

    // this is for the Conditional trait
    /** @return self */
    public function when($value = null, ?callable $callback = null, ?callable $default = null);

    public function getDomain(): ?string;
}
