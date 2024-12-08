<?php

namespace CashDash\Zaar;

use CashDash\Zaar\Auth\Directive;
use CashDash\Zaar\Auth\Guard;
use CashDash\Zaar\Concerns\ShopifyRepositoryInterface;
use CashDash\Zaar\Concerns\ShopifySessionsRepositoryInterface;
use CashDash\Zaar\Concerns\UserRepositoryInterface;
use CashDash\Zaar\Http\Middleware\AddEmbeddedCspHeaderMiddleware;
use CashDash\Zaar\Sessions\CustomSessionManager;
use Illuminate\Auth\RequestGuard;
use Illuminate\Console\Command;
use Illuminate\Contracts\Auth\Factory;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

use function Laravel\Prompts\select;

class ZaarServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('zaar')
            ->hasConfigFile()
            ->hasMigrations([
                'create_shopifies_table',
                'create_shopify_sessions_table',
                'add_shopify_user_id_to_users_table',
            ])
            ->hasInstallCommand(function (\Spatie\LaravelPackageTools\Commands\InstallCommand $command) {
                $command
                    ->startWith(fn () => $this->checkInstallStatus($command))
                    ->publishConfigFile()
                    ->publishMigrations()
                    ->askToRunMigrations()
                    ->askToStarRepoOnGitHub('spatie/laravel-package-tools');
            });

    }

    public function packageRegistered(): void
    {
        config([
            'auth.guards.shopify' => array_merge([
                'driver' => 'shopify',
                'provider' => 'users',
            ], config('auth.guards.shopify', [])),
        ]);
    }

    public function boot(): void
    {
        parent::boot();

        if (config('zaar.force_embedded_https') && Zaar::isEmbedded()) {
            URL::forceScheme('https');
        }

        $this->app->singleton('session', function ($app) {
            return new CustomSessionManager($app);
        });

        $this->configureGuard();
        $this->disableCsrfForPackageRoutes();
        $this->registerBladeDirectives();
        $this->bindRepositories();

        // TODO: for some reason this is not working
        $this->app->booted(function (Application $kernel) {
            $this->app['router']->prependMiddlewareToGroup('web', AddEmbeddedCspHeaderMiddleware::class);
        });
    }

    private function bindRepositories(): void
    {
        app()->bind(UserRepositoryInterface::class, config('zaar.repositories.user.type'));
        app()->bind(ShopifyRepositoryInterface::class, config('zaar.repositories.shopify.type'));

        $sessionDriver = config('zaar.default_session_repository');
        app()->bind(ShopifySessionsRepositoryInterface::class, config("zaar.repositories.sessions.$sessionDriver.type"));
    }

    protected function registerBladeDirectives(): void
    {
        $this->callAfterResolving('blade.compiler', function ($blade) {
            $blade->directive('zaarHead', [Directive::class, 'compileHead']);
        });
    }

    protected function disableCsrfForPackageRoutes(): void
    {
        $routes = config('zaar.disabled_csrf_routes', ['*']);
        if (! is_array($routes)) {
            $routes = [$routes];
        }

        if (empty($routes)) {
            return;
        }

        $this->app->resolving(VerifyCsrfToken::class, function ($middleware) use ($routes) {
            $middleware->except($routes);
        });
    }

    /**
     * Register the guard.
     *
     * @param  Factory  $auth
     * @param  array  $config
     */
    protected function createGuard($auth, $config): RequestGuard
    {
        return new RequestGuard(
            new Guard($auth, $config['provider']),
            request(),
            $auth->createUserProvider($config['provider'] ?? null)
        );
    }

    /**
     * Configure the Sanctum authentication guard.
     */
    protected function configureGuard(): void
    {
        Auth::resolved(function ($auth) {
            $auth->extend('shopify', function ($app, $name, array $config) use ($auth) {
                return tap($this->createGuard($auth, $config), function ($guard) {
                    app()->refresh('request', $guard, 'setRequest');
                });
            });
        });
    }

    private function checkInstallStatus(Command $command)
    {
        $sessionDriver = config('session.driver');
        if ($sessionDriver === 'cookie') {
            $command->fail('You are using the cookie session driver, Zaar is incompatible with this driver. Please change your session driver in session.php to any other driver.');
        }

        $hasExistingShopifyModel = select('Do you have an existing Shopify model?', ['Yes', 'No'], default: 'No') === 'Yes';
        $addAxiosInterceptor = select('Would you like to add an Axios interceptor to automatically send the session token with every request?', ['Yes', 'No'], default: 'Yes') === 'Yes';
        $installZaar = select('Would you like to automatically include the @zaarHead Blade directive?', ['Yes', 'No'], default: 'Yes') === 'Yes';

        if ($hasExistingShopifyModel) {
            $command->error('You have an existing Shopify model, you will need to manually adjust the config and migrations to match your model.');
        }

        if ($addAxiosInterceptor) {
            $command->comment('Adding Axios interceptor...');
            $this->addAxiosInterceptorToApp($command);
        }

        if ($installZaar) {
            $command->comment('Installing @zaarHead directive...');

            $blade = resource_path('views/app.blade.php');
            if (! file_exists($blade)) {
                $command->warn('We were unable to detect your app.blade.php file, you may need to manually add the @zaar directive to your blade file.');
            } else {
                $contents = file_get_contents($blade);
                if (! str_contains($contents, '@zaar')) {
                    $contents = str_replace('</head>', "\t@zaar\n\n\t</head>", $contents);
                    file_put_contents($blade, $contents);
                } else {
                    $command->comment('@zaarHead directive already installed.');
                }
            }
        }
    }

    private function addAxiosInterceptorToApp(Command $command): void
    {
        $bootstrap = base_path('resources/js/bootstrap.ts');
        if (! file_exists($bootstrap)) {
            $bootstrap = base_path('resources/js/bootstrap.js');
        }

        if (! $bootstrap) {
            $command->error("We were unable to detect your bootstrap.ts/js file, if you use Axios, you'll need to add an interceptor to send the session token with every request.");
        }

        $code = <<<'CODE'
window.axios.interceptors.request.use(async function (config) {
    if (!window.shopify) {
        return config;
    }

    const token = await window.shopify.idToken();
    config.headers['Authorization'] = `Bearer ${token}`;
    return config;
});
CODE;

        // quckly check if it already idToken usage
        $contents = file_get_contents($bootstrap);
        if (! str_contains($contents, 'axios.interceptors.request')) {
            file_put_contents($bootstrap, $code, FILE_APPEND);
        } else {
            if (str_contains($contents, 'window.shopify.idToken')) {
                $command->comment('Axios interceptor already installed.');

                return;
            }
            $command->warn('It seems you already have have an Axios interceptor in your bootstrap.ts/js file, you may need to adjust the code to include the session token.');
        }
    }
}
