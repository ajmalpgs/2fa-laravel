<?php

namespace TwoFA\LaravelMfa\Facades;

use Illuminate\Support\Facades\Facade;

class Mfa extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'mfa';
    }
}
