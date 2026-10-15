<?php

namespace TwoFA\LaravelMfa\Contracts;

interface MfaProviderInterface
{
    public function generateSecret(): string;

    public function getProvisioningUri($user, string $secret): string;

    public function verify(string $secret, string $code): bool;

    public function getCurrentTimeStep(): int;
}
