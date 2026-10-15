<?php

namespace TwoFA\LaravelMfa\Services;

use TwoFA\LaravelMfa\Contracts\MfaRequirementResolverInterface;

class DefaultMfaRequirementResolver implements MfaRequirementResolverInterface
{
    public function requiresMfa($user): bool
    {
        if (! config('mfa.enabled', true)) {
            return false;
        }

        if (config('mfa.policy') === 'mandatory') {
            return true;
        }

        return app(MfaManager::class)->settingFor($user)->enabled ?? false;
    }
}
