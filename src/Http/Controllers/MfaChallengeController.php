<?php

namespace TwoFA\LaravelMfa\Http\Controllers;

use App\Models\Admodels\PostModel;
use App\Models\CountryMasterModel;
use TwoFA\LaravelMfa\Http\Requests\VerifyMfaRequest;
use TwoFA\LaravelMfa\Services\MfaManager;
use TwoFA\LaravelMfa\Support\MfaTranslator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;

class MfaChallengeController
{
    public function show(Request $request, MfaManager $mfa)
    {
        if (! $this->hasActiveChallenge($request, $mfa)) {
            return $this->logoutToLogin($request, $mfa);
        }

        return view('mfa::challenge', $this->viewData());
    }

    public function verify(VerifyMfaRequest $request, MfaManager $mfa)
    {
        if (! $mfa->verifyChallenge($request, $request->input('code'))) {
            return back()->withErrors(['code' => MfaTranslator::get('invalid_code')]);
        }

        $url = $request->session()->pull(config('mfa.intended_session_key'), config('mfa.redirect_after_verify'));

        return redirect()->to($url);
    }

    public function recovery(Request $request, MfaManager $mfa)
    {
        if (! $this->hasActiveChallenge($request, $mfa)) {
            return $this->logoutToLogin($request, $mfa);
        }

        return view('mfa::challenge', $this->viewData(['recovery' => true]));
    }

    public function logout(Request $request, MfaManager $mfa)
    {
        return $this->logoutToLogin($request, $mfa);
    }

    private function hasActiveChallenge(Request $request, MfaManager $mfa): bool
    {
        return Auth::check() && $mfa->pendingChallenge($request) !== null;
    }

    private function logoutToLogin(Request $request, MfaManager $mfa)
    {
        $mfa->clearSession($request);
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    private function viewData(array $data = []): array
    {
        return array_merge([
            'pageTitle' => MfaTranslator::get('challenge_title'),
            'mfaTrans' => MfaTranslator::class,
            'websiteSettings' => $this->websiteSettings(),
            'countrySettings' => $this->countrySettings(),
            'countryDetails' => null,
            'menuClass' => '',
            'lang' => App::getLocale(),
            'changeLang' => App::getLocale() === 'ar' ? 'en' : 'ar',
            'user' => auth()->user(),
            'userObj' => auth()->user(),
            'currentURI' => url()->current(),
            'currentRoute' => Route::currentRouteName(),
            'visionsList' => collect(),
            'buttons' => [],
            'menuPosition' => 'left',
            'hasPluploader' => false,
            'hasTextEditor' => false,
            'canUserDeleteRecord' => false,
        ], $data);
    }

    private function countrySettings()
    {
        if (! class_exists(CountryMasterModel::class)) {
            return null;
        }

        return Cache::rememberForever('country_setting', function () {
            return CountryMasterModel::find(1);
        });
    }

    private function websiteSettings()
    {
        if (! class_exists(PostModel::class)) {
            return null;
        }

        return PostModel::where('post_type', 'setting')->first();
    }
}
