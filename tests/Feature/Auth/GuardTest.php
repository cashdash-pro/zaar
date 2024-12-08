<?php

// Provider Tests
use CashDash\Zaar\Auth\Guard;
use CashDash\Zaar\Auth\Provider;
use Illuminate\Contracts\Auth\Factory;

test('guard can get user from valid token', function () {
    // Arrange
    $auth = mock(Factory::class);
    $provider = mock(Provider::class);
    $guard = new Guard($auth, $provider);

    $request = Request::create('/test', 'GET');
    $request->headers->set('Authorization', 'Bearer valid-token');

    app()->instance('request', $request);

    $user = new \Workbench\App\Models\User;
    $provider->shouldReceive('retrieveByToken')
        ->with(null, 'valid-token')
        ->once()
        ->andReturn($user);

    $result = $guard->user();

    expect($result)->toBe($user);
});

test('guard returns null for invalid token', function () {
    $auth = mock(Factory::class);
    $provider = mock(Provider::class);
    $guard = new Guard($auth, $provider);

    $result = $guard->user();

    expect($result)->toBeNull();
});
