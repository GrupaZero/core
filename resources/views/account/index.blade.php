@extends('gzero-core::layouts.master')

@section('title')
    @lang('gzero-core::user.my_account')
@stop

@component('gzero-core::account.menu')@endcomponent

@component('gzero-core::sections.content', ['class' => 'col-sm-8'])
    <h1 class="page-header">@lang('gzero-core::user.my_account')</h1>

    <h3>{{ $user->first_name }} {{ $user->lastName }}</h3>

    @if(isset($user->hasSocialIntegrations) && strpos($user->email,'social_') !== false)
        <p>@lang('gzero-core::common.account_connected')</p>
        <p class="text-danger"><i class="fa fa-exclamation-triangle"><!-- icon --></i> @lang('common.email_is_missing')</p>
    @else
        <p>
            <strong>@choice('gzero-core::common.email', 1):</strong> {{ $user->email }}
            <small class="help-block">@lang('gzero-core::common.email_is_hidden')</small>
        </p>
    @endif

    <a href="{{ route('account.edit') }}" title="@lang('gzero-core::user.edit_account')" class="btn btn-default">
        @lang('gzero-core::user.edit_account')
    </a>

    <a href="{{ route('logout') }}" title="@lang('gzero-core::common.logout')" class="btn btn-danger">
        @lang('gzero-core::common.logout')
    </a>
@endcomponent
