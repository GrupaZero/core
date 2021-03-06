<?php /* @var $user \Gzero\Core\ViewModels\UserViewModel */ ?>
@extends('gzero-core::layouts.master')

@section('title')
    @lang('gzero-core::user.my_account')
@stop

@component('gzero-core::account._menu')@endcomponent

@section('breadcrumbs')
    {!! Breadcrumbs::render() !!}
@stop

@component('gzero-core::layouts._contentSection', ['class' => 'col-sm-8'])
    <h1 class="mt-4">@lang('gzero-core::user.my_account')</h1>

    <h3>{{ $user->firstName() }} {{ $user->lastName() }}</h3>

    @if($user->hasSocialIntegrations() && strpos($user->email(),'social_') !== false)
        <p>@lang('gzero-core::common.account_connected')</p>
        <p class="text-danger"><i class="fa fa-exclamation-triangle"><!-- icon --></i> @lang('common.email_is_missing')</p>
    @else
        <p>
            <strong>@choice('gzero-core::common.email', 1):</strong> {{ $user->email() }}
            <small class="help-block">@lang('gzero-core::common.email_is_hidden')</small>
        </p>
    @endif

    <p>
        <strong>@lang('gzero-core::user.choose_preferred_language'):</strong>
        @lang('gzero-core::' . ($user->languageCode() ? 'language_names.' . $user->languageCode() : 'common.no_option_chosen'))
    </p>

    <p>
        <strong>@lang('gzero-core::user.choose_preferred_timezone'):</strong>
        @if(empty($user->timezone()))
            @lang('gzero-core::common.no_option_chosen')
        @else
            {{ $timezone = $user->timezone() }}
        @endif
    </p>

    <a href="{{ route('account.edit') }}" title="@lang('gzero-core::user.edit_account')" class="btn btn-outline-primary">
        @lang('gzero-core::user.edit_account')
    </a>

    <a href="{{ route('logout') }}" title="@lang('gzero-core::common.logout')" class="btn btn-danger">
        @lang('gzero-core::common.logout')
    </a>
@endcomponent
