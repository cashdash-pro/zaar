<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Shopify App Configuration
    |--------------------------------------------------------------------------
    |
    | This section contains the core configuration for your Shopify app.
    | It includes API credentials, version settings, and session management.
    | The session_type can be either 'online' or 'offline':
    | - online: Requires user authentication for each session
    | - offline: Persists authentication across sessions
    |
    */
    'shopify_app' => [
        'client_id' => env('SHOPIFY_CLIENT_ID'),
        'client_secret' => env('SHOPIFY_CLIENT_SECRET'),

        // only required for socialite
        'scopes' => env('SHOPIFY_SCOPES'),
        'redirect' => env('SHOPIFY_REDIRECT_URI'),

        'api_version' => env('SHOPIFY_API_VERSION', '2024-10'),
        'session_type' => env('SHOPIFY_SESSION_TYPE', \CashDash\Zaar\SessionType::OFFLINE),
    ],

    /*
    |--------------------------------------------------------------------------
    | Authentication Guards
    |--------------------------------------------------------------------------
    |
    | Specify the authentication guards that will be used for Shopify auth.
    | By default, this uses the 'web' guard. For non-embedded apps, that
    | means your users will authenticate like normal. Additionally, you can
    | add other guards like 'sanctum' if you need to.
    |
    */
    'guards' => ['web'],

    /*
    |--------------------------------------------------------------------------
    | HTTPS for Embedded Apps
    |--------------------------------------------------------------------------
    |
    | Force HTTPS for embedded apps. This is required for Shopify's App Bridge
    | to function properly in embedded mode.
    |
    */
    'force_embedded_https' => true,

    /*
    |--------------------------------------------------------------------------
    | Socialite Integration
    |--------------------------------------------------------------------------
    |
    | Configuration for Laravel Socialite integration with Shopify.
    | When enabled, this allows OAuth-based authentication flow.
    | You'll need to provide the redirect route for the OAuth flow,
    | but Zaar will handle the rest.
    |
    */
    'socialite' => [
        'enabled' => false,
        'home_route' => 'dashboard',
    ],

    /*
    |--------------------------------------------------------------------------
    | CSRF Protection
    |--------------------------------------------------------------------------
    |
    | Routes that should be excluded from CSRF verification (embedded apps)
    | The default '*' excludes all routes - modify as needed for security.
    |
    */
    'disabled_csrf_routes' => ['*'],

    /*
    |--------------------------------------------------------------------------
    | Session Repository
    |--------------------------------------------------------------------------
    |
    | The default repository type for storing Shopify sessions.
    | Supports 'database' storage out of the box.
    |
    */
    'default_session_repository' => 'database',

    /*
    |--------------------------------------------------------------------------
    | Middleware Groups
    |--------------------------------------------------------------------------
    |
    | Define middleware groups for different types of Shopify requests:
    | - shopify: Loads the Shopify auth guard
    | - shopify:web: Full web middleware stack with embedded app support
    | - shopify:public: For public/extension endpoints
    |
    */
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
            \CashDash\Zaar\Http\Middleware\SwapSessionStore::class,
            'web',
            'auth:shopify',
            \CashDash\Zaar\Http\Middleware\EnsureSessionStartedMiddleware::class,
        ],
        'shopify:public' => [
            \CashDash\Zaar\Http\Middleware\AuthenticateExtensionRequestMiddleware::class,
            \CashDash\Zaar\Http\Middleware\EnsureSessionStartedMiddleware::class,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Data Repositories
    |--------------------------------------------------------------------------
    |
    | Configure the repositories used for storing different types of data:
    | - user: For managing user data and authentication
    | - shopify: For store/shop related data
    | - sessions: For managing Shopify API sessions
    |
    | Each repository can be customized with its own model and configuration.
    |
    */
    'repositories' => [
        'user' => [
            'type' => CashDash\Zaar\Repositories\UserRepository::class,
            'model' => \App\Models\User::class,
            'email_column' => 'email',
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
