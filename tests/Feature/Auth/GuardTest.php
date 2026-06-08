<?php

use CashDash\Zaar\Auth\Guard;
use Illuminate\Auth\RequestGuard;
use Illuminate\Contracts\Auth\Factory;
use Illuminate\Contracts\Auth\Guard as GuardContract;
use Illuminate\Http\Request;
use Workbench\App\Models\User;

test('shopify guard resolves through the auth manager', function () {
    config()->set('auth.guards.shopify', [
        'driver' => 'shopify',
        'provider' => 'users',
    ]);

    expect(auth()->guard('shopify'))->toBeInstanceOf(RequestGuard::class);
});

test('guard returns the user resolved by configured guards', function () {
    $auth = mock(Factory::class);
    $request = Request::create('/test', 'GET');
    $request->setLaravelSession(app('session.store'));

    app()->instance('request', $request);

    $webGuard = mock(GuardContract::class);
    $user = new User;

    $auth->shouldReceive('guard')
        ->with('web')
        ->once()
        ->andReturn($webGuard);

    $webGuard->shouldReceive('user')
        ->once()
        ->andReturn($user);

    $result = (new Guard($auth, 'users'))($request);

    expect($result)->toBe($user);
});

test('guard returns null without an authenticated user', function () {
    $auth = mock(Factory::class);
    $request = Request::create('/test', 'GET');
    $request->setLaravelSession(app('session.store'));

    app()->instance('request', $request);

    $webGuard = mock(GuardContract::class);

    $auth->shouldReceive('guard')
        ->with('web')
        ->once()
        ->andReturn($webGuard);

    $webGuard->shouldReceive('user')
        ->once()
        ->andReturnNull();

    $result = (new Guard($auth, 'users'))($request);

    expect($result)->toBeNull();
});
