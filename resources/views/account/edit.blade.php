<?php /* @var $user \Gzero\Core\ViewModels\UserViewModel */ ?>
@extends('gzero-core::layouts.master')

@section('title')
    @lang('gzero-core::user.edit_account')
@stop

@component('gzero-core::account._menu')@endcomponent

@component('gzero-core::layouts._contentSection', ['class' => 'col-sm-8'])
    <h1 class="page-header">@lang('gzero-core::user.edit_account')</h1>
    @if(!$isUserEmailSet)
        <div class="alert alert-info">
            <i class="fa fa-info-circle"><!-- icon --></i> @lang('gzero-core::common.set_email_to_edit_account')
        </div>
    @endif
    <div class="row">
        <div class="col-md-6">
            <form id="edit-account-form" action="#" method="POST" role="form">
                @if($isUserEmailSet)
                    <div class="form-group">
                        <label class="control-label" for="name">@lang('gzero-core::common.nick_name')</label>
                        <input type="text" id="name" name="name" value="{{ $user->name() }}"
                               class="form-control{{ $errors->first('name') ? ' is-invalid' : '' }}"
                               placeholder="@lang('gzero-core::common.nick_name')">
                        @if($errors->first('name'))
                            <div class="invalid-feedback">{{ $errors->first('name') }}</div>
                        @endif
                    </div>
                @else
                    <div class="form-group">
                        <label class="control-label">@lang('gzero-core::common.nick_name')</label>
                        <p class="form-control-static">{{ $user->name() }}</p>
                        <input type="hidden" name="name" value="{{ $user->name() }}">
                    </div>
                @endif
                <div class="form-group">
                    <label class="control-label" for="email">@choice('gzero-core::common.email', 1)</label>
                    <input type="email" id="email" name="email" value="{{ $user->email() }}"
                           class="form-control{{ $errors->first('email') ? ' is-invalid' : '' }}"
                           placeholder="@choice('gzero-core::common.email', 1)">
                    @if($errors->first('email'))
                        <div class="invalid-feedback">{{ $errors->first('email') }}</div>
                    @endif
                </div>
                @if($isUserEmailSet)
                    <div class="form-group">
                        <label class="control-label" for="first_name">@lang('gzero-core::common.first_name')</label>
                        <input type="text" id="first_name" name="first_name" value="{{ $user->firstName() }}"
                               class="form-control{{ $errors->first('first_name') ? ' is-invalid' : '' }}"
                               placeholder="@lang('gzero-core::common.first_name')">
                        @if($errors->first('first_name'))
                            <div class="invalid-feedback">{{ $errors->first('first_name') }}</div>
                        @endif
                    </div>
                    <div class="form-group">
                        <label class="control-label" for="last_name">@lang('gzero-core::common.last_name')</label>
                        <input type="text" id="last_name" name="last_name" value="{{ $user->lastName() }}"
                               class="form-control{{ $errors->first('last_name') ? ' is-invalid' : '' }}"
                               placeholder="@lang('gzero-core::common.last_name')">
                        @if($errors->first('last_name'))
                            <div class="invalid-feedback">{{ $errors->first('last_name') }}</div>
                        @endif
                    </div>
                @else
                    <div class="form-group">
                        <label class="control-label">@lang('gzero-core::common.first_name')</label>
                        <p class="form-control-static">{{ $user->firstName() }}</p>
                    </div>
                    <div class="form-group">
                        <label class="control-label">@lang('gzero-core::common.last_name')</label>
                        <p class="form-control-static">{{ $user->lastName() }}</p>
                    </div>
                @endif
                @if($isUserEmailSet)
                    @if($user->password())
                        <div class="separator">
                            <span>@lang('gzero-core::common.password_change')</span>
                        </div>
                        <p class="text-muted">
                            <i class="fa fa-info-circle"><!-- icon --></i> @lang('gzero-core::common.leave_blank')
                        </p>
                    @else
                        <div class="separator">
                            <span>@lang('gzero-core::common.password_set')</span>
                        </div>
                        <p class="text-success">
                            <i class="fa fa-info-circle"><!-- icon --></i> @lang('gzero-core::common.set_password_to_login')
                        </p>
                    @endif
                    <div class="form-group">
                        <label class="control-label" for="password">@lang('gzero-core::common.new_password')</label>
                        <input type="password" id="password" name="password"
                               class="form-control{{ $errors->first('password') ? ' is-invalid' : '' }}"
                               placeholder="@lang('gzero-core::common.new_password')">
                        @if($errors->first('password'))
                            <p class="invalid-feedback">{{ $errors->first('password') }}</p>
                        @endif
                    </div>
                    <div class="form-group">
                        <label class="control-label" for="passwordConfirmation">@lang('gzero-core::common.password_repeat')</label>
                        <input type="password" id="passwordConfirmation" name="password_confirmation"
                               class="form-control{{ $errors->first('password_confirmation') ? ' is-invalid' : '' }}"
                               placeholder="@lang('gzero-core::common.password_repeat')">
                        @if($errors->first('password_confirmation'))
                            <div class="invalid-feedback">{{ $errors->first('password_confirmation') }}</div>
                        @endif
                    </div>
                @endif
                <button id="edit-account" type="submit" class="btn btn-primary">@lang('gzero-core::common.save')</button>
            </form>
        </div>
    </div>
@endcomponent
@push('footer')
    <script type="text/javascript">
        $(function() {
            $('#edit-account').click(function(event) {
                event.preventDefault();
                Loading.start('#main-container');
                $.ajax({
                    url: "{{ request()->getScheme() }}://api.{{ request()->getHTTPHost() }}/v1/user/account",
                    headers: {'X-CSRF-TOKEN': Laravel.csrfToken},
                    xhrFields: {
                        withCredentials: true
                    },
                    data: $('#edit-account-form').serializeObject(),
                    type: 'PUT',
                    success: function(xhr) {
                        @if($isUserEmailSet)
                             Loading.stop();
                        // set success message
                        setGlobalMessage('success', "@lang('gzero-core::common.changes_saved_message')");
                        hideMessages();
                        clearFormValidationErrors();
                        @else
                            location.reload();
                        @endif
                    },
                    error: function(xhr) {
                        Loading.stop();
                        if (typeof xhr.responseJSON !== 'undefined' && xhr.status === 422) {
                            // clear previous errors
                            clearFormValidationErrors();
                            $.each(xhr.responseJSON.error.errors, function(index, error) {
                                // set form errors
                                setFormValidationErrors(index, error);
                            });
                        }
                    }
                });
            })
        });
    </script>
@endpush
