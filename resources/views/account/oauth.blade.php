@extends('gzero-core::layouts.master')

@section('title')
    @lang('gzero-core::user.oauth')
@stop

@component('gzero-core::account.menu')@endcomponent

@component('gzero-core::sections.content', ['class' => 'col-sm-8'])
    <h1 class="page-header">@lang('gzero-core::user.oauth')</h1>

    <passport-clients></passport-clients>
    <passport-authorized-clients></passport-authorized-clients>
    <passport-personal-access-tokens></passport-personal-access-tokens>
@endcomponent
