<?php

namespace TwoFA\LaravelMfa\Services;

use TwoFA\LaravelMfa\Models\MfaSetting;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class RecoveryCodeService
{
    public function regenerate(MfaSetting $setting): array
    {
        $setting->recoveryCodes()->delete();
        $codes = [];

        for ($i = 0; $i < (int) config('mfa.recovery_codes', 8); $i++) {
            $code = $this->makeCode();
            $codes[] = $code;
            $setting->recoveryCodes()->create(['code_hash' => Hash::make($code)]);
        }

        return $codes;
    }

    public function consume(MfaSetting $setting, string $code): bool
    {
        $plain = strtoupper(trim($code));

        foreach ($setting->recoveryCodes()->whereNull('used_at')->get() as $recoveryCode) {
            if (Hash::check($plain, $recoveryCode->code_hash)) {
                $recoveryCode->forceFill(['used_at' => now()])->save();

                return true;
            }
        }

        return false;
    }

    private function makeCode(): string
    {
        return collect(str_split(strtoupper(Str::random((int) config('mfa.recovery_code_length', 10))), 5))->implode('-');
    }
}
