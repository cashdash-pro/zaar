<?php

namespace CashDash\Zaar\Contracts;

use CashDash\Zaar\Dtos\OfflineSessionData;
use CashDash\Zaar\Dtos\OnlineSessionData;

interface ShopifySessionsRepositoryInterface
{
    public function findOnline(string $session_id): ?OnlineSessionData;

    public function createOnline(OnlineSessionData $sessionData): void;

    public function findOffline(string $domain): ?OfflineSessionData;

    public function createOffline(OfflineSessionData $sessionData): void;

    public function onlineSessionFor(int $shopifyUserId): ?OnlineSessionData;

    public function deleteAll(string $domain): void;
}
