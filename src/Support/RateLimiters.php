<?php

namespace TwoFA\LaravelMfa\Support;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class RateLimiters
{
    public static function register(): void
    {
        RateLimiter::for('mfa', function (Request $request) {
            return Limit::perMinutes((int) config('mfa.decay_minutes', 1), (int) config('mfa.max_attempts', 5))
                ->by(optional($request->user())->getAuthIdentifier() ?: $request->ip());
        });
    }
}
