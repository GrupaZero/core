@extends('gzero-base::layouts.master')

@section('title')
    @lang('gzero-base::user.my_account')
@stop

@component('gzero-base::account.menu')@endcomponent

@component('gzero-base::sections.content', ['class' => 'col-sm-8'])
    <h1 class="page-header">@lang('gzero-base::user.my_account')</h1>

    <h3>{{ $user->first_name }} {{ $user->lastName }}</h3>

    @if(isset($user->hasSocialIntegrations) && strpos($user->email,'social_') !== false)
        <p>@lang('gzero-base::common.account_connected')</p>
        <p class="text-danger"><i class="fa fa-exclamation-triangle"><!-- icon --></i> @lang('common.email_is_missing')</p>
    @else
        <p>
            <strong>@choice('gzero-base::common.email', 1):</strong> {{ $user->email }}
            <small class="help-block">@lang('gzero-base::common.email_is_hidden')</small>
        </p>
    @endif

    <a href="{{ route('account.edit') }}" title="@lang('gzero-base::user.edit_account')" class="btn btn-default">
        @lang('gzero-base::user.edit_account')
    </a>

    <a href="{{ route('logout') }}" title="@lang('gzero-base::common.logout')" class="btn btn-danger">
        @lang('gzero-base::common.logout')
    </a>
@endcomponent
