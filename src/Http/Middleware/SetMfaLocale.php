<?php

namespace TwoFA\LaravelMfa\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class SetMfaLocale
{
    public function handle($request, Closure $next)
    {
        if (! Session::has('lang')) {
            Session::put('lang', config('app.locale', 'en'));
        }

        App::setLocale(Session::get('lang', config('app.locale', 'en')));

        return $next($request);
    }
}
