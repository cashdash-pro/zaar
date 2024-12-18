<?php

use CashDash\Zaar\Http\Controllers\SocialiteController;

Route::get('/auth/token/reauthenticate', function () {
    return view('zaar::auth');
});

if (config('zaar.socialite.enabled')) {
    Route::middleware('web')->group(function () {
        Route::match(['get', 'post'],'/auth/shopify', [SocialiteController::class, 'redirect'])->name('auth.shopify');
        Route::get('/auth/shopify/callback', [SocialiteController::class, 'callback'])->name('auth.shopify.callback');
    });
}
