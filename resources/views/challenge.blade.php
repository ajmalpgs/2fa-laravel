@extends('admin.layouts.login_master')
@php($mfaTrans = \TwoFA\LaravelMfa\Support\MfaTranslator::class)

@section('styles')
@parent
{{ Html::style('assets/frontend/dist/styles/mfa.css?098910') }}
@stop

@section('metatags')
    <meta name="description" content="{{ @$websiteSettings->site_meta_description }}" />
    <meta name="keywords" content="{{ @$websiteSettings->site_meta_keyword }}" />
    <meta name="author" content="{{ @$websiteSettings->site_meta_title }}" />
@stop

@section('seoPageTitle')
<title>{{ $pageTitle }}</title>
@stop

@section('content')
<section class="login-section" id="login">
    @if(!empty($countrySettings))
        <div class="bg_image b-lazy" data-src="{{ $countrySettings->getBannerImage() }}"></div>
    @endif

    <div class="login_container mfa-challenge">
        <div class="logo_box">
            @if(!empty($countrySettings))
                <img src="{{ $countrySettings->getLogoImage() }}" alt="{{ $countrySettings->getName() }}">
                <h1 class="title_">{{ $countrySettings->getName() }}</h1>
            @endif
        </div>

        <div class="mfa-challenge-copy">
            <h2>{{ $mfaTrans::get('challenge_title') }}</h2>
            <p>{{ $mfaTrans::get(!empty($recovery) ? 'recovery_prompt' : 'challenge_prompt') }}</p>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger">{{ $errors->first() }}</div>
        @endif

        <form method="post" action="{{ route(config('mfa.route_name_prefix').'challenge.verify') }}" class="dash_form_controller mfa-challenge-form">
            @csrf
            <div class="d_f_col full_">
                <div class="input-field">
                    <label for="mfa-challenge-code">{{ $mfaTrans::get(!empty($recovery) ? 'recovery_code' : 'code') }}</label>
                    <div class="input_">
                        <input id="mfa-challenge-code" type="text" name="code" class="form-control" autocomplete="one-time-code" required autofocus @if (empty($recovery)) inputmode="numeric" maxlength="{{ config('mfa.totp.digits', 6) }}" @endif>
                        <div class="pass_helper tooltip">
                            <i class="icon icon-lock"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d_f_col full_">
                <div class="btn_group center_">
                    <button type="submit" class="btn_primary"><span>{{ $mfaTrans::get('verify') }}</span></button>
                </div>
            </div>

            <div class="d_f_col full_">
                @if (!empty($recovery))
                    <a href="{{ route(config('mfa.route_name_prefix').'challenge') }}" class="forget_linkt">
                        <span>{{ $mfaTrans::get('use_authenticator') }}</span>
                    </a>
                @else
                    <a href="{{ route(config('mfa.route_name_prefix').'recovery') }}" class="forget_linkt">
                        <span>{{ $mfaTrans::get('use_recovery') }}</span>
                    </a>
                @endif
            </div>
        </form>

        <form method="post" action="{{ route(config('mfa.route_name_prefix').'challenge.logout') }}" class="mfa-challenge-signout" id="mfa-signout-form">
            @csrf
            <button type="submit">{{ $mfaTrans::get('sign_in_another_account') }}</button>
        </form>
    </div>

    <div class="lang_btn">
        <a href="{{ route('admin_language_switcher', [$changeLang]) }}" class="lang_link">
            <span>{!! ($lang == 'en') ? '&#1593;' : 'En' !!}</span>
        </a>
    </div>
    @if(!empty($countrySettings) && $countrySettings->getData('cm_hide_country_logo') == 2)
        <div class="uae_logo_container">
            <label>{{ ($lang == 'en') ? 'In corporation with' : 'بالتعاون مع ' }}</label>
            <div class="img_ b-lazy" data-src="{{ asset('assets/admin/2fa/images/icon-uae.svg') }}"></div>
        </div>
    @endif
</section>
@stop

@section('scripts')
<script type="text/javascript" src="{{ asset('assets/admin/2fa/scripts/lib/jquery-3.6.0.min.js') }}"></script>
<script src="{{ asset('assets/admin/2fa/scripts/lib/jquery-migrate-3.3.2.min.js') }}"></script>
<script src="{{ asset('assets/admin/2fa/scripts/min/home_plugins.js') }}"></script>
<script src="{{ asset('assets/admin/2fa/scripts/min/home.js') }}"></script>
<script>
    (function () {
        var entries = window.performance && performance.getEntriesByType ? performance.getEntriesByType('navigation') : [];
        var isReload = entries.length ? entries[0].type === 'reload' : performance.navigation && performance.navigation.type === 1;
        var form = document.getElementById('mfa-signout-form');

        if (isReload && form) {
            form.submit();
        }
    })();
</script>
@stop
