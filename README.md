# Zaar - Laravel Shopify Authentication Made Easy

[![Latest Version on Packagist](https://img.shields.io/packagist/v/nick-potts/laravel-shopify.svg?style=flat-square)](https://packagist.org/packages/nick-potts/laravel-shopify)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/nick-potts/laravel-shopify/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/nick-potts/laravel-shopify/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/nick-potts/laravel-shopify/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/nick-potts/laravel-shopify/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/nick-potts/laravel-shopify.svg?style=flat-square)](https://packagist.org/packages/nick-potts/laravel-shopify)

## Overview

Zaar is a Laravel package that simplifies Shopify authentication for your Laravel applications. It provides seamless integration for both embedded and external Shopify apps, handling session management, authentication flows, and user management.

## Features

- 🔒 Secure authentication for embedded and external Shopify apps
- 🔄 Session management with online/offline token support
- 🛡️ Built-in CSRF protection configuration
- 🔌 Easy integration with Laravel's authentication system
- 📱 Support for both web and API authentication
- 🎯 Public app endpoints support
- ⚡ Automatic Axios interceptor setup for session tokens

## Installation

```bash
composer require cashdash-pro/zaar

php artisan zaar:install
```

The install command will:
1. Publish the configuration file
2. Create necessary migrations
3. Set up Axios interceptors (optional)
4. Add the `@zaarHead` directive to your Blade layout (optional)

## Core Concepts

### Authentication Flow and Events

Zaar implements different authentication strategies but follows a consistent flow through the `run()` method. Here's how it works:

```php
// From HasAuthEvents trait
public function run(?Authenticatable $user): ?Authenticatable
{
    return $this
        ->withOnlineSession($user)    // Step 1
        ->withUser()                  // Step 2
        ->withDomain()                // Step 3
        ->when(                       // Step 4 (Conditional)
            Zaar::sessionType() === SessionType::OFFLINE,
            fn (AuthFlow $auth) => $auth->withOfflineSession()
        )
        ->mergeSessions()             // Step 5
        ->bindData()                  // Step 6
        ->withShopifyModel()          // Step 7
        ->dispatchEvents()            // Step 8
        ->getUser();                  // Final Result
}
```

#### Embedded Apps (Admin Panel)
1. **Online Session** (`withOnlineSession`)
   - Validates Shopify's session token
   - Extracts user and shop information
   - Fires `OnlineSessionLoaded`

2. **User Resolution** (`withUser`)
   - Uses session data to find/create user
   - Can be customized via `findUserUsing`/`createUserUsing`

3. **Domain Resolution** (`withDomain`)
   - Uses domain from session token by default
   - Can be overridden via `setShopifyDomain` (good for store switching)

4. **Offline Session** (Conditional)
   - Only if configured for offline tokens
   - Fires `OfflineSessionLoaded`

#### External Apps (API/Standalone)
The same flow is followed, but with key differences:

1. **Online Session** (`withOnlineSession`)
   - Tries to load the online session from the authenticated user
   - Fires `OnlineSessionLoaded` if successful

2. **User Resolution** (`withUser`)
   - Uses your existing authenticated user (configured via 'guards' in zaar.php)

3. **Domain Resolution** (`withDomain`)
   - Must be explicitly provided via `setShopifyDomain`
   - Critical for determining which store to use

4. **Offline Session** (Conditional)
   - Required for external apps
   - Fires `OfflineSessionLoaded`

#### Common Steps (Both Types)
After strategy-specific steps:

1. **Session Merging** (`mergeSessions`)
   - Combines available session data
   - Creates unified access token available via `Zaar::session()`

2. **Data Binding** (`bindData`)
   - Makes sessions available via container
   - Enables `Zaar::session()`, `Zaar::onlineSession()`, `Zaar::offlineSession()` helpers

3. **Store Loading** (`withShopifyModel`)
   - Loads/creates Shopify store record
   - Fires `ShopifyTenantLoaded`

4. **Event Dispatch** (`dispatchEvents`)
   - Fires all accumulated events
   - Ends with `SessionAuthenticated`


This flow ensures consistent behavior while accommodating the different requirements of embedded and external apps.


### Event Flow and Importance

The events in Zaar are fired in a specific order through `dispatchEvents()`, each serving a crucial purpose:

1. **`OnlineSessionLoaded`**
   - Fired when an online session token is validated
   - Contains user identity from Shopify
   - Perfect for tracking user activity or session starts
   - Only fires for embedded apps or when online token exists

2. **`OfflineSessionLoaded`**
   - Fired when offline token is available
   - Critical for setting up API access
   - Use this to initialize API clients or background job configurations
   - Contains the permanent access token

3. **`ShopifyTenantLoaded`**
   - **Most important event for multi-tenant apps**
   - Fired when the Shopify store model is loaded
   - This is your chance to:
     - Set up database connections
     - Initialize tenant-specific services
     - Configure API settings
     - Load store preferences
   - Always fires regardless of authentication type

4. **`SessionAuthenticated`**
   - Final event with complete context
   - Provides access to:
     - Session data (merged online/offline)
     - Shopify store model
     - Authenticated user
   - Perfect for:
     - Logging successful authentications
     - Starting background processes
     - Initializing store-specific features

### Additional Critical Events

These events fire during specific operations:

- **`ShopifyFoundEvent`**
   - Fires when an existing store is found
   - Critical for:
     - Updating store metadata
     - Syncing store settings
     - Checking for plan changes
     - Validating store status

- **`ShopifyCreated`**
   - Fires for new store installations
   - Use for:
     - Initial store setup
     - Creating default settings
     - Welcome notifications
     - First-time configurations

- **`ShopifyUserCreated`**
   - Fires when a new user is created
   - Perfect for:
     - Setting up user preferences
     - Sending welcome emails
     - Initial role assignment

- **`ShopifyOnlineSessionCreated`/`ShopifyOfflineSessionCreated`**
   - Fire when new sessions are created
   - Use for:
     - Token storage
     - Session monitoring
     - Access logging

The event system is designed to give you complete control over the authentication and initialization process. Each event provides specific context and timing for different aspects of your application's setup.


### Domain Resolution

The most important configuration in Zaar is setting up how shop domains are resolved. This controls which store is loaded for both embedded and external apps:

```php
use CashDash\Zaar\Facades\Zaar;

Zaar::setShopifyDomain(function (Request $request, User $user, ?string $current_domain) {
    // For external apps (outside Shopify Admin), you MUST return the shop domain
    if (!Zaar::isEmbedded()) {
        return $request->header('X-Shopify-Shop-Domain');
        // or from query params: return $request->get('shop');
        // or from route parameters: return $request->route('shop');
    }

    // For embedded apps, you can override the domain to switch stores
    // The $user parameter lets you check permissions
    if ($user->can('access', $otherStore)) {
        return 'other-store.myshopify.com';
    }

    // Return null to use the domain from the session token
    return null;
});
```

This resolver is called during the authentication flow and determines which store's data and sessions are loaded. For external apps, you must return a domain. For embedded apps, returning `null` will use the domain from Shopify's session token.

## Usage

### Middleware Types

1. **Embedded Apps** (`shopify.web`)
   - For apps within Shopify Admin iframe
   - Handles session token exchange
   - Includes necessary headers
   - Example:
   ```php
   Route::middleware('shopify.web')->group(function () {
       Route::get('/app', function () {
           // Your embedded app's main entry point
       });
   });
   ```

2. **External Apps** (`shopify`)
   - For standalone/API applications
   - Uses offline tokens
   - No iframe handling
   - Example:
   ```php
   Route::middleware('shopify')->group(function () {
       Route::get('/api/products', function () {
           $session = Zaar::offlineSession();
       });
   });
   ```

3. **Public Endpoints** (`shopify.public`)
   - For public-facing endpoints
   - Limited shop context
   - Example:
   ```php
   Route::middleware('shopify.public')->group(function () {
       Route::get('/public/products', function () {
           // Public endpoint logic
       });
   });
   ```

### Session Management

1. **Accessing Sessions**
   ```php
   // Get current session
   $session = Zaar::session();
   $accessToken = $session->accessToken;
   $shop = $session->shop;

   // Check session type
   if (Zaar::sessionType() === SessionType::ONLINE) {
       // Online session logic
   }
   ```

2. **Manual Session Control**
   ```php
   // Start session for different store
   Zaar::startSessionManually($newShop, $user);

   // Handle expired sessions
   if (!Zaar::sessionStarted()) {
       Zaar::clearExpiredSessionsAndReauthenticate($domain);
   }
   ```

### User Management

```php
use CashDash\Zaar\Dtos\OnlineSessionData;

// Custom user lookup
Zaar::findUserUsing(function (OnlineSessionData $session) {
    return User::where('email', $session->email)
              ->orWhere('shopify_id', $session->sub)
              ->first();
});

// Custom user creation
Zaar::createUserUsing(function (OnlineSessionData $session) {
    return User::create([
        'name' => $session->name,
        'email' => $session->email,
        'shopify_id' => $session->sub
    ]);
});
```

## Configuration

### Environment Setup
```env
SHOPIFY_CLIENT_ID=your_client_id
SHOPIFY_CLIENT_SECRET=your_client_secret
SHOPIFY_API_VERSION=2024-01
SHOPIFY_SESSION_TYPE=offline # or online
SHOPIFY_SCOPES=read_products,write_products
SHOPIFY_REDIRECT_URI=https://your-app.com/auth/callback
```

### Package Configuration
```php
// config/zaar.php
return [
    'shopify_app' => [
        'client_id' => env('SHOPIFY_CLIENT_ID'),
        'client_secret' => env('SHOPIFY_CLIENT_SECRET'),
        'scopes' => env('SHOPIFY_SCOPES'),
        'redirect' => env('SHOPIFY_REDIRECT_URI'),
        'api_version' => env('SHOPIFY_API_VERSION', '2024-01'),
        'session_type' => env('SHOPIFY_SESSION_TYPE', 'OFFLINE'),
    ],
    'guards' => 'web',
    'force_embedded_https' => true,
    'disabled_csrf_routes' => ['*'],
    'default_session_repository' => 'database',
];
```

### Frontend Integration

The package automatically sets up Axios interceptors:

```javascript
window.axios.interceptors.request.use(async function (config) {
    if (!window.shopify) {
        return config;
    }

    const token = await window.shopify.idToken();
    config.headers['Authorization'] = `Bearer ${token}`;
    config.headers['X-Referrer'] = window.location.href;

    return config;
});
```

## Advanced Usage


### Repository Configuration

```php
'repositories' => [
    'user' => [
        'type' => YourUserRepository::class,
        'model' => User::class,
        'email_column' => 'email',
    ],
    'shopify' => [
        'type' => YourShopifyRepository::class,
        'model' => Shopify::class,
        'shop_domain_column' => 'domain',
    ],
    'sessions' => [
        'database' => [
            'type' => YourSessionRepository::class,
            'model' => ShopifySession::class,
        ],
    ],
],
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [CashDash](https://github.com/nick-potts)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
