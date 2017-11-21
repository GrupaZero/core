@extends('gzero-core::emails.layouts.default')

@section('emailContent')
    <h1>@lang('gzero-core::emails.welcome.title')</h1>

    <p>
        @lang('gzero-core::emails.welcome.body', ["siteName" => option('general', 'site_name'), "domain" => config('gzero.domain')])
    </p>
@stop
