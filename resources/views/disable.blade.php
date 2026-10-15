@extends(config('mfa.views.layout'))
@php($mfaTrans = \TwoFA\LaravelMfa\Support\MfaTranslator::class)

@section('styles')
@parent
{{ Html::style('assets/frontend/dist/styles/mfa.css?098911') }}
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
                                        <div class="mfa-disable">
                                            <h2>{{ $mfaTrans::get('disable_title') }}</h2>
                                            <p>{{ $mfaTrans::get('disable_prompt') }}</p>
                                            <form method="post" action="{{ route(config('mfa.route_name_prefix').'disable') }}" class="btn_group center_ gap_">
                                                @csrf
                                                <button type="submit" class="btn_primary mfa-disable-confirm"><span>{{ $mfaTrans::get('disable') }}</span></button>
                                                <a href="{{ route(config('mfa.route_name_prefix').'setup') }}" class="btn_primary grey_"><span>{{ $mfaTrans::get('cancel') }}</span></a>
                                            </form>
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
