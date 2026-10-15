<?php

namespace TwoFA\LaravelMfa\Contracts;

interface MfaRequirementResolverInterface
{
    public function requiresMfa($user): bool;
}
