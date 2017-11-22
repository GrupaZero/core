@extends('gzero-core::layouts.master')

@section('title')
    @lang('gzero-core::common.welcome')
@stop

@section('content')
    <div class="col-md-4 col-md-offset-4 text-center">
        <h1 class="page-header">@lang('gzero-core::common.welcome')!</h1>
        <p class="lead">@lang('gzero-core::common.welcome_message')</p>
        <hr>
        <a href="{{ route('account') }}" class="btn btn-primary btn-lg btn-block mb20">
            @lang('gzero-core::user.my_account')
        </a>
        <a href="{{ routeMl('home') }}" class="btn btn-default btn-lg btn-block">
            @lang('gzero-core::common.back_to_homepage')
        </a>
    </div>
@stop
