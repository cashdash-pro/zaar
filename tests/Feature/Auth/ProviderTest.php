<?php

use CashDash\Zaar\Repositories\UserRepository;
use Workbench\App\Models\User;

beforeEach(function () {
    $this->repository = new UserRepository;
});

test('user repository returns user when email exists', function () {
    $user = User::factory()->create(['email' => 'shopify-user@example.com']);

    $result = $this->repository->find('shopify-user@example.com');

    expect($result)->toBeInstanceOf(User::class)
        ->and($result->is($user))->toBeTrue();
});

test('user repository returns null when email is missing', function () {
    expect($this->repository->find(null))->toBeNull();
});
