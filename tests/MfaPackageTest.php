<?php

namespace TwoFA\LaravelMfa\Tests;

use TwoFA\LaravelMfa\MfaServiceProvider;
use TwoFA\LaravelMfa\Models\MfaRecoveryCode;
use TwoFA\LaravelMfa\Services\RecoveryCodeService;
use Illuminate\Foundation\Auth\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Orchestra\Testbench\TestCase;

class MfaPackageTest extends TestCase
{
    use RefreshDatabase;

    protected function getPackageProviders($app)
    {
        return [MfaServiceProvider::class];
    }

    protected function defineDatabaseMigrations()
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }

    public function test_recovery_codes_are_hashed_and_single_use(): void
    {
        $user = new User();
        $user->id = 1;
        $setting = app('mfa')->beginSetup($user)[0];

        $codes = app(RecoveryCodeService::class)->regenerate($setting);

        $this->assertNotSame($codes[0], MfaRecoveryCode::first()->code_hash);
        $this->assertTrue(app(RecoveryCodeService::class)->consume($setting, $codes[0]));
        $this->assertFalse(app(RecoveryCodeService::class)->consume($setting, $codes[0]));
    }
}
