<?php

namespace TwoFA\LaravelMfa\Http\Middleware;

use Closure;
use TwoFA\LaravelMfa\Services\MfaManager;

class EnsureMfaVerified
{
    public function handle($request, Closure $next)
    {
        $mfa = app(MfaManager::class);

        if ($request->user() && $mfa->requiresMfa($request->user()) && ! $mfa->isVerified($request)) {
            $request->session()->put(config('mfa.intended_session_key'), $request->fullUrl());

            $setting = $mfa->settingFor($request->user());

            if (! $setting || ! $setting->enabled) {
                return redirect()->route(config('mfa.route_name_prefix').'setup');
            }

            $mfa->createChallenge($request->user(), $request);

            return redirect()->route(config('mfa.route_name_prefix').'challenge');
        }

        return $next($request);
    }
}
