@extends(config('mfa.views.layout'))
@php($mfaTrans = \TwoFA\LaravelMfa\Support\MfaTranslator::class)

@section('styles')
@parent
{{ Html::style('assets/frontend/dist/styles/mfa.css?098912') }}
@stop

@section(config('mfa.views.content_section'))
<main class="dashboard_">
    <div class="dash-container">
        <div class="p-row">
            <div class="p-col"></div>
            <div class="p-col">
                <div class="dash_body_pannel overflow_">
                    <div class="admin_common_table">
                        <table>
                            <thead>
                                <tr>
                                    <th><div class="common_title white_ lg_">{{ $mfaTrans::get('security_title') }}</div></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        <div class="mfa-recovery">
                                            <h2>{{ $mfaTrans::get('recovery_codes') }}</h2>
                                            <p>{{ $mfaTrans::get('recovery_codes_notice') }}</p>
                                            <ul class="mfa-recovery-codes">
                                                @foreach ($codes as $code)
                                                    <li><code>{{ $code }}</code></li>
                                                @endforeach
                                            </ul>
                                            <div class="btn_group center_">
                                                <a href="{{ route(config('mfa.route_name_prefix').'setup') }}" class="btn_primary"><span>{{ $mfaTrans::get('back_to_security') }}</span></a>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
@stop
