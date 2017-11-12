@extends('gzero-base::layouts.master')

@section('title')
    @lang('gzero-base::common.password_reset')
@stop

<!-- Main Content -->
@section('content')
    <div class="row justify-content-md-center">
        <div class="col-md-4">
            <h1 class="page-header">@lang('gzero-base::common.password_reset')</h1>
            @if (session('status'))
                <div class="alert alert-success">
                    {{ session('status') }}
                </div>
            @endif

            <form role="form" method="POST" action="{{ route('post.password.email') }}">
                {{ csrf_field() }}

                <div class="form-group">
                    <label for="email" class="control-label">@choice('gzero-base::common.email', 1)</label>
                    <input id="email" type="email" class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }}"
                    name="email" value="{{ old('email') }}" required>
                    @if ($errors->has('email'))
                        <div class="invalid-feedback">{{ $errors->first('email') }}</div>
                    @endif
                </div>

                <div class="form-group">
                    <button type="submit" class="btn btn-primary btn-lg btn-block">
                        @lang('gzero-base::common.send_password_reset_link')
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
