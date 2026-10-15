<?php

namespace TwoFA\LaravelMfa\Http\Middleware;

use Closure;
use TwoFA\LaravelMfa\Services\MfaManager;

class RequireMfa
{
    public function handle($request, Closure $next)
    {
        if ($request->user() && app(MfaManager::class)->requiresMfa($request->user())) {
            return $next($request);
        }

        abort(403);
    }
}
