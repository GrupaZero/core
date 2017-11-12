@extends('gzero-base::layouts.master')

@section('title')
    @lang('gzero-base::user.oauth')
@stop

@component('gzero-base::account.menu')@endcomponent

@component('gzero-base::sections.content', ['class' => 'col-sm-8'])
    <h1 class="page-header">@lang('gzero-base::user.oauth')</h1>

    <passport-clients></passport-clients>
    <passport-authorized-clients></passport-authorized-clients>
    <passport-personal-access-tokens></passport-personal-access-tokens>
@endcomponent
