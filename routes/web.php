<?php

use App\Http\Controllers\Admin\UsersController;
use TwoFA\LaravelMfa\Http\Controllers\MfaChallengeController;
use TwoFA\LaravelMfa\Http\Controllers\MfaSetupController;
use Illuminate\Support\Facades\Route;

Route::group([
    'prefix' => config('mfa.route_prefix', 'mfa'),
    'as' => config('mfa.route_name_prefix', 'mfa.'),
    'middleware' => config('mfa.middleware', ['web']),
], function () {
    Route::middleware(config('mfa.auth_middleware', ['auth']))->group(function () {
        Route::get('setup', [MfaSetupController::class, 'show'])->name('setup');
        Route::post('setup/verify', [MfaSetupController::class, 'verify'])->name('setup.verify')->middleware('throttle:mfa');
        Route::match(['get', 'post'], 'disable', [MfaSetupController::class, 'disable'])->name('disable');
        Route::post('recovery/regenerate', [MfaSetupController::class, 'regenerate'])->name('recovery.regenerate');
    });

    Route::get('challenge', [MfaChallengeController::class, 'show'])->name('challenge');
    Route::post('challenge/verify', [MfaChallengeController::class, 'verify'])->name('challenge.verify')->middleware('throttle:mfa');
    Route::post('challenge/logout', [MfaChallengeController::class, 'logout'])->name('challenge.logout');
    Route::get('recovery', [MfaChallengeController::class, 'recovery'])->name('recovery');
});

Route::group([
    'prefix' => config('app.admin_prefix').'/users',
    'middleware' => array_merge(
        ['web', 'auth', 'IsAdmin', 'XSS', 'AdminLanguageSwitcher', 'DateFormatConvertor'],
        ['mfa.verified', 'ForcePasswordChange']
    ),
], function () {
    Route::post('reset-mfa/{id}', [UsersController::class, 'resetMfa'])->name('reset_user_mfa_admin');
});
