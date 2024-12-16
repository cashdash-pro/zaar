<?php

use CashDash\Zaar\Actions\TokenExchangeAuth\DecodeShopifySessionToken;
use CashDash\Zaar\Dtos\SessionToken;

test('retrieveByToken processes valid JWT token', function () {
    // Arrange
    $sid = 'sid';
    $token = createJwtToken(sid: $sid);

    $result = DecodeShopifySessionToken::make()->handle($token);

    // Assert
    expect($result)
        ->toBeInstanceOf(SessionToken::class)
        ->and($result->sid)->toBe($sid);
});

test('retrieveByToken handles token failures', function () {
    // Arrange
    $token = createJwtToken('12345', -3600);

    $result = DecodeShopifySessionToken::make()->handle($token);

    // Act & Assert
    expect($result)
        ->toBeNull();
});
