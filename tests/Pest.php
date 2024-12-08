<?php

use CashDash\Zaar\Tests\TestCase;
use Workbench\App\Models\User;

uses(TestCase::class)->in(__DIR__)
    ->beforeEach(function () {
        Config::set([
            'zaar.user.model' => User::class,
            'zaar.user.shopify_user_id_column' => 'shopify_user_id',
            'zaar.shopify_app.client_secret' => 'test-secret',
            'zaar.user.auto_create' => false,
        ]);
    });

function createJwtToken(
    string $sub = '12345',
    int $exp = 3600,
    string $sid = 'session_456'
): string {
    return \Firebase\JWT\JWT::encode([
        'iss' => 'shop123.myshopify.com/admin',
        'dest' => 'shop123.myshopify.com',
        'aud' => 8273642,
        'sub' => $sub,
        'exp' => time() + $exp,
        'nbf' => time(),
        'iat' => time(),
        'jti' => 'jwt_123',
        'sid' => $sid,
        'si' => 'session_456',
        'sig' => 'a1b2c3d4e5f6',
    ], config('zaar.shopify_app.client_secret'), 'HS256');
}
