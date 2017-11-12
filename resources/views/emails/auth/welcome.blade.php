@extends('gzero-base::emails.layouts.default')

@section('emailContent')
    <h1>@lang('gzero-base::emails.welcome.title')</h1>

    <p>
        @lang('gzero-base::emails.welcome.body', ["siteName" => option('general', 'site_name'), "domain" => config('gzero.domain')])
    </p>
@stop
