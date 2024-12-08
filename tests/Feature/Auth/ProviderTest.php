<?php

use CashDash\Zaar\Actions\TokenExchangeAuth\ExchangeForSessionData;
use CashDash\Zaar\Auth\Provider;
use CashDash\Zaar\Dtos\OnlineSessionData;
use Illuminate\Support\Facades\Config;
use Workbench\App\Models\User;

beforeEach(function () {
    $this->provider = new Provider;

    Config::set([
        'zaar.user.model' => User::class,
        'zaar.user.shopify_user_id_column' => 'shopify_user_id',
        'zaar.shopify_app.client_secret' => 'test-secret',
        'zaar.user.auto_create' => false,
    ]);
});

dataset('validUserIds', [
    'numeric' => 12345,
    'longer' => 12345654,
]);

test('retrieveById returns user when exists', function (int $userId) {
    // Arrange
    $user = User::factory()->create(['shopify_user_id' => $userId]);

    // Act
    $result = $this->provider->retrieveById($userId);

    // Assert
    expect($result)
        ->toBeInstanceOf(User::class)
        ->and($result->shopify_user_id)->toBe($userId);
})->with('validUserIds');

test('retrieveById returns null for nonexistent user', function () {
    expect($this->provider->retrieveById('nonexistent'))->toBeNull();
});

test('retrieveByToken processes valid JWT token', function () {
    $userId = 12345654;
    $token = createJwtToken($userId);
    $onlineSession = OnlineSessionData::mock();
    // idk why this doesn't get resolved out of the container?
    $exchanger = ExchangeForSessionData::mock();
    app()->instance(ExchangeForSessionData::class, $exchanger);
    $exchanger->shouldReceive('handleOnline')
        ->andReturn($onlineSession);

    User::factory()->create(['shopify_user_id' => $userId]);

    $result = $this->provider->retrieveByToken(null, $token);

    // Assert
    expect($result)
        ->toBeInstanceOf(User::class)
        ->and($result->shopify_user_id)->toBe($userId);
});
