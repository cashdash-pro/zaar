<?php

return [
    'shopify_app' => [
        'client_id' => env('SHOPIFY_APP_CLIENT_ID'),
        'client_secret' => env('SHOPIFY_APP_CLIENT_SECRET'),
        'api_version' => env('SHOPIFY_API_VERSION', '2024-10'),
        'session_type' => env('SHOPIFY_SESSION_TYPE', \CashDash\Zaar\SessionType::OFFLINE),
    ],

    'guards' => 'web',

    'auto_create_user' => true,

    'force_embedded_https' => true,

    'disabled_csrf_routes' => ['*'],

    'default_session_repository' => 'database',

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
            'type' => CashDash\Zaar\Repositories\ShopifyRepositoryInterace::class,
            'model' => \CashDash\Zaar\Models\Shopify::class,
            'shop_domain_column' => 'domain',
        ],

        'sessions' => [
            'database' => [
                'type' => CashDash\Zaar\Repositories\Sessions\ShopifySessionRepository::class,
                'model' => \CashDash\Zaar\Models\ShopifySession::class,
            ],
        ],
    ],
];
