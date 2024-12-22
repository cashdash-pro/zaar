<?php

namespace CashDash\Zaar\Http\Controllers;

use CashDash\Zaar\Actions\Creation\UserCreation;
use CashDash\Zaar\Auth\Strategies\ExternalStrategy;
use CashDash\Zaar\Contracts\ShopifyRepositoryInterface;
use CashDash\Zaar\Contracts\ShopifySessionsRepositoryInterface;
use CashDash\Zaar\Contracts\UserRepositoryInterface;
use CashDash\Zaar\Dtos\OfflineSessionData;
use CashDash\Zaar\Dtos\OnlineSessionData;
use CashDash\Zaar\Events\ShopifyOfflineSessionCreated;
use CashDash\Zaar\Events\ShopifyOnlineSessionCreated;
use CashDash\Zaar\SessionType;
use CashDash\Zaar\Zaar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Laravel\Socialite\Facades\Socialite;

class SocialiteController
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private ShopifySessionsRepositoryInterface $shopifySessionsRepository,
        private ShopifyRepositoryInterface $shopifyRepository
    ) {}

    public function redirect(Request $request)
    {

        $data = $request->validate([
            'domain' => 'required',
            'offline' => 'nullable|boolean',
        ]);
        $shop = $data['domain'];
        $offline = $data['offline'] ?? false;

        // strip any .myshopify.com from the domain
        $shop = \Str::before($shop, '.myshopify.com');

        $config = [
            'subdomain' => $shop,
        ];

        if (! $offline) {
            $config['grant_options[]'] = 'per-user';
        }

        $res = Socialite::driver('shopify')
            ->with($config)
            ->redirect();

        // check if is inertia
        if ($request->header('X-Inertia')) {
            return Inertia::location($res->getTargetUrl());
        } else {
            return $res;
        }
    }

    public function callback(Request $request)
    {
        $response = Socialite::driver('shopify')->user();

        $isOnline = $response->accessTokenResponseBody['associated_user'] ?? null !== null;

        if ($isOnline) {
            $onlineSessionData = OnlineSessionData::fromTokenResponse(
                (string) \Str::uuid(),
                $response->user['myshopifyDomain'],
                $response->accessTokenResponseBody);

            $this->shopifySessionsRepository->createOnline($onlineSessionData);

            app()->instance(OnlineSessionData::class, $onlineSessionData);

            event(new ShopifyOnlineSessionCreated($onlineSessionData));

            $user = $this->userRepository->find($onlineSessionData->email);
            if (! $user) {
                $user = UserCreation::make()->handle($onlineSessionData);
            }
            Auth::login($user);

            $request->session()->regenerate();
            $request->session()->put(ExternalStrategy::SESSION_DOMAIN, $response->user['myshopifyDomain']);

            if (Zaar::sessionType() === SessionType::OFFLINE) {
                return redirect()->route('auth.shopify', [
                    'domain' => $response->user['myshopifyDomain'],
                    'offline' => true,
                ]);
            }
        } else {
            $offlineSessionData = OfflineSessionData::fromTokenResponse(
                $response->user['myshopifyDomain'],
                $response->accessTokenResponseBody);

            $this->shopifySessionsRepository->createOffline($offlineSessionData);

            app()->instance(OfflineSessionData::class, $offlineSessionData);

            event(new ShopifyOfflineSessionCreated($offlineSessionData));

            if (! auth()->check()) {
                return redirect()->route('auth.shopify', [
                    'domain' => $response->user['myshopifyDomain'],
                    'offline' => false,
                ]);
            }
        }

        return redirect()->intended(route(config('zaar.socialite.home_route', 'dashboard')));
    }
}
