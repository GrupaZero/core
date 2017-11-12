@extends('gzero-base::layouts.master')

@section('title')
    @lang('gzero-base::common.welcome')
@stop

@section('content')
    <div class="col-md-4 col-md-offset-4 text-center">
        <h1 class="page-header">@lang('gzero-base::common.welcome')!</h1>
        <p class="lead">@lang('gzero-base::common.welcome_message')</p>
        <hr>
        <a href="{{ route('account') }}" class="btn btn-primary btn-lg btn-block mb20">
            @lang('gzero-base::user.my_account')
        </a>
        <a href="{{ routeMl('home') }}" class="btn btn-default btn-lg btn-block">
            @lang('gzero-base::common.back_to_homepage')
        </a>
    </div>
@stop
