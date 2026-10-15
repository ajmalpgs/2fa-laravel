<?php

namespace TwoFA\LaravelMfa\Services;

use TwoFA\LaravelMfa\Contracts\MfaProviderInterface;
use PragmaRX\Google2FA\Google2FA;

class TotpMfaProvider implements MfaProviderInterface
{
    private $google2fa;

    public function __construct(Google2FA $google2fa)
    {
        $this->google2fa = $google2fa;
        $this->google2fa->setOneTimePasswordLength((int) config('mfa.totp.digits', 6));
        $this->google2fa->setKeyRegeneration((int) config('mfa.totp.period', 30));
    }

    public function generateSecret(): string
    {
        return $this->google2fa->generateSecretKey();
    }

    public function getProvisioningUri($user, string $secret): string
    {
        $label = method_exists($user, 'getEmail') ? $user->getEmail() : ($user->email ?? (string) $user->getAuthIdentifier());

        return $this->google2fa->getQRCodeUrl(config('mfa.issuer'), $label, $secret);
    }

    public function verify(string $secret, string $code): bool
    {
        return (bool) $this->google2fa->verifyKey($secret, preg_replace('/\s+/', '', $code), (int) config('mfa.totp.window', 1));
    }

    public function getCurrentTimeStep(): int
    {
        return (int) floor(time() / (int) config('mfa.totp.period', 30));
    }
}
