<?php

use TwoFA\LaravelMfa\Services\DefaultMfaRequirementResolver;
use TwoFA\LaravelMfa\Services\TotpMfaProvider;

return [
    'enabled' => env('MFA_ENABLED', true),
    'issuer' => env('MFA_ISSUER', config('app.name', 'Laravel')),
    'provider' => env('MFA_PROVIDER', 'totp'),
    'policy' => env('MFA_POLICY', 'optional'),
    'requirement_resolver' => DefaultMfaRequirementResolver::class,
    'providers' => [
        'totp' => TotpMfaProvider::class,
    ],
    'route_prefix' => env('MFA_ROUTE_PREFIX', 'mfa'),
    'route_name_prefix' => env('MFA_ROUTE_NAME_PREFIX', 'mfa.'),
    'middleware' => ['web', 'mfa.locale'],
    'auth_middleware' => ['auth'],
    'views' => [
        'layout' => env('MFA_LAYOUT', 'admin.layouts.master'),
        'content_section' => env('MFA_CONTENT_SECTION', 'content'),
    ],
    'verified_session_key' => 'mfa_verified_at',
    'pending_session_key' => 'mfa_pending_challenge_id',
    'intended_session_key' => 'mfa_intended_url',
    'challenge_timeout' => 300,
    'max_attempts' => 5,
    'decay_minutes' => 1,
    'recovery_codes' => 8,
    'recovery_code_length' => 10,
    'qr' => [
        'size' => 220,
        'margin' => 1,
    ],
    'redirect_after_verify' => '/',
    'redirect_after_setup' => '/',
    'totp' => [
        'digits' => 6,
        'period' => 30,
        'window' => 1,
        'algorithm' => 'sha1',
    ],
];
