<?php

namespace TwoFA\LaravelMfa\Http\Controllers;

use TwoFA\LaravelMfa\Events\RecoveryCodesRegenerated;
use TwoFA\LaravelMfa\Http\Requests\VerifyMfaRequest;
use TwoFA\LaravelMfa\Services\MfaManager;
use TwoFA\LaravelMfa\Services\QrCodeService;
use TwoFA\LaravelMfa\Services\RecoveryCodeService;
use TwoFA\LaravelMfa\Support\MfaTranslator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Route;

class MfaSetupController
{
    public function show(Request $request, MfaManager $mfa, QrCodeService $qrCodes)
    {
        $enabledSetting = $mfa->settingFor($request->user());

        if ($enabledSetting && $enabledSetting->enabled) {
            return view('mfa::setup', $this->viewData([
                'setting' => $enabledSetting,
                'provisioningUri' => null,
                'qrCodeUri' => null,
            ]));
        }

        [$setting, $uri] = $mfa->beginSetup($request->user());

        return view('mfa::setup', $this->viewData([
            'setting' => $setting,
            'provisioningUri' => $uri,
            'qrCodeUri' => $qrCodes->dataUri($uri),
        ]));
    }

    public function verify(VerifyMfaRequest $request, MfaManager $mfa)
    {
        [$valid, $codes] = $mfa->confirmSetup($request->user(), $request->input('code'));

        if (! $valid) {
            return back()->with('otperror', MfaTranslator::get('invalid_code'));
        }

        $request->session()->put(config('mfa.verified_session_key'), now()->timestamp);

        return view('mfa::recovery-codes', $this->viewData(['codes' => $codes]));
    }

    public function regenerate(Request $request, MfaManager $mfa, RecoveryCodeService $recoveryCodes)
    {
        $setting = $mfa->settingFor($request->user());
        abort_unless($setting && $setting->enabled, 403);

        $codes = $recoveryCodes->regenerate($setting);
        event(new RecoveryCodesRegenerated($setting));

        return view('mfa::recovery-codes', $this->viewData(['codes' => $codes]));
    }

    public function disable(Request $request, MfaManager $mfa)
    {
        if ($request->isMethod('post')) {
            $mfa->disable($request->user());
            $mfa->clearSession($request);

            return redirect(config('mfa.redirect_after_setup'))->with('status', MfaTranslator::get('disabled'));
        }

        return view('mfa::disable', $this->viewData());
    }

    private function viewData(array $data = []): array
    {
        return array_merge([
            'pageTitle' => MfaTranslator::get('security_title'),
            'mfaTrans' => MfaTranslator::class,
            'websiteSettings' => null,
            'countrySettings' => null,
            'menuClass' => '',
            'lang' => App::getLocale(),
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
}
