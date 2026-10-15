<?php

namespace TwoFA\LaravelMfa\Services;

use TwoFA\LaravelMfa\Contracts\MfaProviderInterface;
use TwoFA\LaravelMfa\Contracts\MfaRequirementResolverInterface;
use TwoFA\LaravelMfa\Events\MfaChallengeCreated;
use TwoFA\LaravelMfa\Events\MfaChallengeFailed;
use TwoFA\LaravelMfa\Events\MfaChallengeSucceeded;
use TwoFA\LaravelMfa\Events\MfaDisabled;
use TwoFA\LaravelMfa\Events\MfaEnabled;
use TwoFA\LaravelMfa\Events\RecoveryCodeUsed;
use TwoFA\LaravelMfa\Models\MfaChallenge;
use TwoFA\LaravelMfa\Models\MfaSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MfaManager
{
    private $resolver;
    private $recoveryCodes;

    public function __construct(MfaRequirementResolverInterface $resolver, RecoveryCodeService $recoveryCodes)
    {
        $this->resolver = $resolver;
        $this->recoveryCodes = $recoveryCodes;
    }

    public function settingFor($user, string $provider = null): ?MfaSetting
    {
        return MfaSetting::where('user_type', get_class($user))
            ->where('user_id', $user->getAuthIdentifier())
            ->where('provider', $provider ?: config('mfa.provider', 'totp'))
            ->first();
    }

    public function provider(string $name = null): MfaProviderInterface
    {
        $class = config('mfa.providers.'.($name ?: config('mfa.provider', 'totp')));

        return app($class);
    }

    public function requiresMfa($user): bool
    {
        return $this->resolver->requiresMfa($user);
    }

    public function isVerified(Request $request, $user = null): bool
    {
        $user = $user ?: $request->user();

        return $user
            && $request->session()->has(config('mfa.verified_session_key'))
            && ($this->settingFor($user)->enabled ?? false);
    }

    public function beginSetup($user): array
    {
        $provider = config('mfa.provider', 'totp');
        $setting = $this->settingFor($user, $provider);

        if ($setting && ! $setting->enabled && $setting->secret) {
            return [$setting, $this->provider($provider)->getProvisioningUri($user, $setting->secret)];
        }

        $secret = $this->provider($provider)->generateSecret();
        $setting = MfaSetting::updateOrCreate([
            'user_type' => get_class($user),
            'user_id' => $user->getAuthIdentifier(),
            'provider' => $provider,
        ], [
            'secret' => $secret,
            'enabled' => false,
            'confirmed_at' => null,
            'last_used_timestep' => null,
        ]);

        return [$setting, $this->provider($provider)->getProvisioningUri($user, $secret)];
    }

    public function confirmSetup($user, string $code): array
    {
        $setting = $this->settingFor($user);

        if (! $setting || ! $this->provider($setting->provider)->verify($setting->secret, $code)) {
            return [false, []];
        }

        $setting->forceFill([
            'enabled' => true,
            'confirmed_at' => now(),
            'last_used_timestep' => $this->provider($setting->provider)->getCurrentTimeStep(),
        ])->save();

        $codes = $this->recoveryCodes->regenerate($setting);
        event(new MfaEnabled($setting));

        return [true, $codes];
    }

    public function disable($user): void
    {
        if ($setting = $this->settingFor($user)) {
            $setting->forceFill(['enabled' => false, 'confirmed_at' => null])->save();
            $setting->recoveryCodes()->delete();
            event(new MfaDisabled($setting));
        }
    }

    public function resetForUser($user): bool
    {
        $setting = $this->settingFor($user);

        if (! $setting) {
            return false;
        }

        $setting->delete();

        return true;
    }

    public function createChallenge($user, Request $request): MfaChallenge
    {
        $setting = $this->settingFor($user);
        $challenge = MfaChallenge::create([
            'mfa_setting_id' => $setting->id,
            'user_type' => get_class($user),
            'user_id' => $user->getAuthIdentifier(),
            'session_id' => $request->session()->getId(),
            'ip_address' => $request->ip(),
            'expires_at' => now()->addSeconds((int) config('mfa.challenge_timeout', 300)),
        ]);

        $request->session()->put(config('mfa.pending_session_key'), $challenge->id);
        event(new MfaChallengeCreated($challenge));

        return $challenge;
    }

    public function pendingChallenge(Request $request): ?MfaChallenge
    {
        $challenge = MfaChallenge::find($request->session()->get(config('mfa.pending_session_key')));

        if (! $challenge || ! $challenge->isUsable($request->session()->getId())) {
            return null;
        }

        return $challenge;
    }

    public function handleAuthenticatedUser($user, Request $request)
    {
        if (! $this->requiresMfa($user)) {
            $request->session()->put(config('mfa.verified_session_key'), now()->timestamp);

            return null;
        }

        $setting = $this->settingFor($user);

        if (! $setting || ! $setting->enabled) {
            return redirect()->route(config('mfa.route_name_prefix').'setup');
        }

        $this->createChallenge($user, $request);

        return redirect()->route(config('mfa.route_name_prefix').'challenge');
    }

    public function verifyChallenge(Request $request, string $code): bool
    {
        $challenge = MfaChallenge::find($request->session()->get(config('mfa.pending_session_key')));

        if (! $challenge || ! $challenge->isUsable($request->session()->getId())) {
            return false;
        }

        $setting = $challenge->setting;
        $provider = $this->provider($setting->provider);
        $valid = $provider->verify($setting->secret, $code);
        $currentStep = $provider->getCurrentTimeStep();

        if ($valid && $setting->last_used_timestep !== null && $setting->last_used_timestep >= $currentStep) {
            $valid = false;
        }

        if (! $valid && $this->recoveryCodes->consume($setting, $code)) {
            $valid = true;
            event(new RecoveryCodeUsed($setting));
        }

        if (! $valid) {
            event(new MfaChallengeFailed($challenge));
            return false;
        }

        $challenge->forceFill(['used_at' => now()])->save();
        $setting->forceFill(['last_used_timestep' => $currentStep])->save();
        $request->session()->forget(config('mfa.pending_session_key'));
        $request->session()->put(config('mfa.verified_session_key'), now()->timestamp);
        $request->session()->regenerate();
        event(new MfaChallengeSucceeded($challenge));

        return true;
    }

    public function clearSession(Request $request): void
    {
        $request->session()->forget([
            config('mfa.verified_session_key'),
            config('mfa.pending_session_key'),
            config('mfa.intended_session_key'),
        ]);
    }
}
