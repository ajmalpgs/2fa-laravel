@extends(config('mfa.views.layout'))
@php($mfaTrans = \TwoFA\LaravelMfa\Support\MfaTranslator::class)
@php($otperror = session('otperror'))
@section('styles')
@parent
{{ Html::style('assets/frontend/dist/styles/mfa.css?098909') }}
@stop
@section(config('mfa.views.content_section'))
<main class="dashboard_">
    <div class="dash-container">
        <div class="p-row">

            <div class="p-col">
             
            </div>

            <div class="p-col">
                <div class="dash_body_pannel overflow_">
                    <div class="admin_common_table">
                        <table>
                            <thead>
                                <tr>
                                    <th colspan="2"><div class="common_title white_ lg_">{{ $mfaTrans::get('security_title') }}</div></th>
                                </tr>
                            </thead>
                            <tbody>
                                @if ($setting->enabled)
                                    <tr>
                                        <td colspan="2">
                                            <div class="list_label">
                                                <strong>{{ $mfaTrans::get('status') }}</strong>
                                                <span>{{ $mfaTrans::get('enabled_notice') }}</span>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="2">
                                            <form method="post" action="{{ route(config('mfa.route_name_prefix').'recovery.regenerate') }}" class="btn_group center_ gap_">
                                                @csrf
                                                <button type="submit" class="btn_primary"><span>{{ $mfaTrans::get('regenerate_recovery_codes') }}</span></button>
                                                <a href="{{ route(config('mfa.route_name_prefix').'disable') }}" class="btn_primary grey_"><span>{{ $mfaTrans::get('disable') }}</span></a>
                                            </form>
                                        </td>
                                    </tr>
                                @else
                                    <tr>
                                        <td colspan="2">
                                            <div class="btn_group center_ gap_">
                                                <button type="button" class="btn_primary" id="mfa-enable-open">
                                                    <span>{{ $mfaTrans::get('enable_mfa') }}</span>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>



        </div>
    </div>
</main>

@if (! $setting->enabled)
    <div class="mfa-setup-wrapper" id="mfa-setup-modal" hidden>
        <div class="mfa-setup-card" role="dialog" aria-modal="true" aria-labelledby="mfa-setup-title">
            <button type="button" class="mfa-setup-close" id="mfa-setup-close" aria-label="{{ $mfaTrans::get('close') }}">&times;</button>
            <div class="mfa-setup-card-header">
                <h2 class="mfa-setup-card-title" id="mfa-setup-title">{{ $mfaTrans::get('setup_title') }}</h2>
                <p class="mfa-setup-card-description">{{ $mfaTrans::get('scan_prompt') }}</p>
            </div>
            <div class="mfa-setup-card-content">
                @if (!empty($qrCodeUri))
                    <div class="mfa-setup-card-content-image">
                        <img src="{{ $qrCodeUri }}" width="{{ config('mfa.qr.size', 220) }}" height="{{ config('mfa.qr.size', 220) }}" alt="{{ $mfaTrans::get('qr_alt') }}">
                    </div>
                @endif
            </div>
            <div class="mfa-setup-card-footer">
                @if (!empty($otperror))
                    <p class="mfa-setup-error">{{ $otperror }}</p>
                @endif
                <form method="post" action="{{ route(config('mfa.route_name_prefix').'setup.verify') }}">
                    @csrf
                    <div class="form-group">
                        <label for="mfa-setup-code">{{ $mfaTrans::get('code') }}</label>
                        <div class="mfa-setup-card-content-input">
                            <input id="mfa-setup-code" name="code" autocomplete="one-time-code" required>
                            <button type="submit"><span>{{ $mfaTrans::get('verify') }}</span></button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endif
@stop

@section('scripts')
@parent
@if (! $setting->enabled)
<script>
    (function () {
        var modal = document.getElementById('mfa-setup-modal');
        var openButton = document.getElementById('mfa-enable-open');
        var closeButton = document.getElementById('mfa-setup-close');
        var codeInput = document.getElementById('mfa-setup-code');

        if (!modal || !openButton || !closeButton) {
            return;
        }

        function openModal() {
            modal.hidden = false;
            modal.classList.add('is-open');
            document.body.classList.add('mfa-modal-open');
            if (codeInput) {
                codeInput.focus();
            }
        }

        function closeModal() {
            modal.hidden = true;
            modal.classList.remove('is-open');
            document.body.classList.remove('mfa-modal-open');
            openButton.focus();
        }

        openButton.addEventListener('click', openModal);
        closeButton.addEventListener('click', closeModal);
        modal.addEventListener('click', function (event) {
            if (event.target === modal) {
                closeModal();
            }
        });
        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && !modal.hidden) {
                closeModal();
            }
        });

        @if (!empty($otperror))
          openModal();
        @endif
    })();
</script>
@endif
@stop
