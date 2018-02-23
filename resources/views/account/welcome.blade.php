@extends('gzero-core::layouts.master')

@section('title')
    @lang('gzero-core::common.welcome')
@stop

@section('content')
    <div class="col-md-4 offset-md-4 text-center mt-4">
        <h1 class="page-header">@lang('gzero-core::common.welcome')!</h1>
        <p class="lead">@lang('gzero-core::common.welcome_message')</p>
        <hr>
        <a href="{{ route('account') }}" class="btn btn-primary btn-lg btn-block mb20">
            @lang('gzero-core::user.my_account')
        </a>
        <a href="{{ route('home') }}" class="btn btn-default btn-lg btn-block">
            @lang('gzero-core::common.back_to_homepage')
        </a>
    </div>
@stop
