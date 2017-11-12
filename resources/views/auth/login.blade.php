@extends('gzero-base::layouts.master')

@section('title')
    @lang('gzero-base::common.login')
@stop

@section('content')
    <div class="row justify-content-md-center">
        <div class="col-md-4">
            <h1 class="page-header">@lang('gzero-base::common.login')</h1>

            <form role="form" method="POST" action="{{ route('post.login') }}">
                {{ csrf_field() }}

                <div class="form-group">
                    <label class="control-label" for="email">@choice('gzero-base::common.email', 1)</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}"
                           class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }}"
                           placeholder="@choice('gzero-base::common.email', 1)" required autofocus>
                    @if ($errors->has('email'))
                        <div class="invalid-feedback">{{ $errors->first('email') }}</div>
                    @endif
                </div>

                <div class="form-group">
                    <label for="password" class="control-label">@lang('gzero-base::common.password')</label>
                    <input id="password" type="password" name="password"
                           class="form-control{{ $errors->has('password') ? ' is-invalid' : '' }}"
                           placeholder="@lang('gzero-base::common.password')" required>
                    @if ($errors->has('password'))
                        <div class="invalid-feedback">{{ $errors->first('password') }}</div>
                    @endif
                </div>
                <div class="form-group">
                    <div class="row">
                        <div class="col-sm-6">
                            <label class="form-check-label">
                                <input type="checkbox" class="form-check-input" name="remember" checked="checked">
                                @lang('gzero-base::common.remember')
                            </label>
                        </div>
                        <div class="col-sm-6">
                            <div class="checkbox text-right">
                                <a href="{{ route('password.request') }}">@lang('gzero-base::common.forgot_password')</a>
                            </div>
                        </div>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary btn-lg btn-block">
                    @lang('gzero-base::common.login')
                </button>
            </form>
            @if(isProviderLoaded('Gzero\Social\ServiceProvider'))
                @include('gzero-social::includes.socialLogin')
            @endif
            <hr/>
            <div class="text-center">
                @lang('gzero-base::common.not_a_member') <a href="{{ route('register') }}"> @lang('gzero-base::common.register')</a>
            </div>
        </div>
    </div>
@endsection
