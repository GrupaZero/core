@section('footer')
    <div class="container">
        <div class="clearfix text-muted">
            <div class="copyrights pull-left">
                Copyright &copy; {{ config('gzero.domain') }},
                @lang('gzero-core::common.all_rights_reserved')
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
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js"
        integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q"
        crossorigin="anonymous"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"
        integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl"
        crossorigin="anonymous"></script>
@if (file_exists(public_path('/js/manifest.js')))
    <script src="{{ mix('/js/manifest.js') }}"></script>
@endif
@if (file_exists(public_path('/js/vendor.js')))
    <script src="{{ mix('/js/vendor.js') }}"></script>
@endif
@if (file_exists(public_path('/js/app.js')))
    <script src="{{ mix('/js/app.js') }}"></script>
@endif
@endpush
