@section('footer')
    <div class="container">
        <div class="clearfix text-muted">
            <div class="copyrights pull-left">
                Copyright &copy; {{ config('gzero.domain') }},
                @lang('gzero-base::common.all_rights_reserved')
            </div>
        </div>
    </div>
    <cookie-law policy-url=""></cookie-law>
    <div class="loading"><!-- loading container --></div>
@show

@push('footer')
<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"
        integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN"
        crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.11.0/umd/popper.min.js"
        integrity="sha384-b/U6ypiBEHpOf/4+1nzFpr53nxSS+GLCkfwBdFNTxtclqqenISfwAzpKaMNFNmj4"
        crossorigin="anonymous"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/js/bootstrap.min.js"
        integrity="sha384-h0AbiXch4ZDo7tp9hKZ4TsHbi047NrKGLO3SEJAg45jXxnGIfYzk4Si90RDIqNm1"
        crossorigin="anonymous"></script>
@if (file_exists(public_path('/js/app.js')))
    <script src="{{ mix('/js/app.js') }}"></script>
@endif
@endpush
