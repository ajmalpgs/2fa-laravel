<?php

namespace TwoFA\LaravelMfa\Support;

class MfaTranslator
{
    public static function get(string $key): string
    {
        $databaseKey = 'mfa_'.$key;

        if (function_exists('lang')) {
            $translated = lang($databaseKey);

            if ($translated !== str_replace('_', ' ', $databaseKey)) {
                return $translated;
            }
        }

        return trans('mfa::mfa.'.$key);
    }
}
