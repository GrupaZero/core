@section('footer')
    <div class="container">
        <div class="clearfix text-muted">
            <div class="copyrights float-left">
                Copyright &copy; {{ config('gzero.domain') }},
                @lang('gzero-core::common.all_rights_reserved')
            </div>
        </div>
    </div>

    <privacy-info privacy-policy-url="{{ url(option('general', 'cookies_policy_url', '')) }}"></privacy-info>
    <div class="loading"><!-- loading container --></div>
@show
