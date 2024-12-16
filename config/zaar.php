<?php

return [
    'shopify_app' => [
        'client_id' => env('SHOPIFY_CLIENT_ID'),
        'client_secret' => env('SHOPIFY_CLIENT_SECRET'),

        // only required for socialite
        'scopes' => env('SHOPIFY_SCOPES'),
        'redirect' => env('SHOPIFY_REDIRECT_URI'),

        'api_version' => env('SHOPIFY_API_VERSION', '2024-10'),
        'session_type' => env('SHOPIFY_SESSION_TYPE', \CashDash\Zaar\SessionType::OFFLINE),
    ],

    'guards' => 'web',

    'force_embedded_https' => true,

    'socialite' => [
        'enabled' => false,
        'home_route' => 'dashboard',
    ],

    'disabled_csrf_routes' => ['*'],

    'default_session_repository' => 'database',

    'middleware' => [
        'shopify' => [
            'auth:shopify',
            \CashDash\Zaar\Http\Middleware\EnsureSessionStartedMiddleware::class,
        ],
        'shopify:web' => [
            \CashDash\Zaar\Http\Middleware\FixReferrerMiddleware::class,
            \CashDash\Zaar\Http\Middleware\AddParamsToRedirectMiddleware::class,
            \CashDash\Zaar\Http\Middleware\AddEmbeddedCspHeaderMiddleware::class,
            \CashDash\Zaar\Http\Middleware\ReauthenticateEmbeddedRequestsMiddleware::class,
            'web',
            'auth:shopify',
            \CashDash\Zaar\Http\Middleware\EnsureSessionStartedMiddleware::class,
        ],
        'shopify:public' => [
            \CashDash\Zaar\Http\Middleware\AuthenticateExtensionRequestMiddleware::class,
        ]
    ],


    /*
     * Data will be stored and loaded from these repositories.
     */
    'repositories' => [
        'user' => [
            'type' => CashDash\Zaar\Repositories\UserRepository::class,
            'model' => \App\Models\User::class,
            'shopify_user_id_column' => 'shopify_user_id',
        ],

        'shopify' => [
            'type' => \CashDash\Zaar\Repositories\ShopifyRepository::class,
            'model' => \CashDash\Zaar\Models\Shopify::class,
            'shop_domain_column' => 'domain',
        ],

        'sessions' => [
            'database' => [
                'type' => \CashDash\Zaar\Repositories\ShopifySessionRepository::class,
                'model' => \CashDash\Zaar\Models\ShopifySession::class,
            ],
        ],
    ],
];
