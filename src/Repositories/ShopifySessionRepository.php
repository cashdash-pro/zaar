<?php

namespace CashDash\Zaar\Repositories;

use CashDash\Zaar\Contracts\ShopifySessionsRepositoryInterface;
use CashDash\Zaar\Dtos\OfflineSessionData;
use CashDash\Zaar\Dtos\OnlineSessionData;

class ShopifySessionRepository implements ShopifySessionsRepositoryInterface
{
    /**
     * @return class-string<\Illuminate\Database\Eloquent\Model>
     */
    private function model(): string
    {
        return config('zaar.repositories.sessions.database.model');
    }

    public function findOnline(string $session_id): ?OnlineSessionData
    {
        return $this->model()::query()
            ->where('is_online', true)
            ->where('id', $session_id)
            ->first()
            ?->toData();
    }

    public function createOffline(OfflineSessionData $sessionData): void
    {
        $this->model()::create($sessionData->toArray())->toData();
    }

    public function createOnline(OnlineSessionData $sessionData): void
    {
        $this->model()::create($sessionData->toArray());
    }

    public function findOffline(string $domain): ?OfflineSessionData
    {
        $session = $this->model()::query()
            ->where('is_online', false)
            ->where('shop', $domain)
            ->first();

        return $session?->toData();
    }

    public function onlineSessionFor(int $shopifyUserId): ?OnlineSessionData
    {
        return $this->model()::query()
            ->where('shopify_user_id', $shopifyUserId)
            ->latest()
            ->first()
            ?->toData();
    }
}
