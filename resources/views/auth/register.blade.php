@extends('gzero-core::layouts.master')

@section('title')
    @lang('gzero-core::common.register')
@stop

@section('content')
    <div class="row justify-content-md-center">
        <div class="col-md-4">
            <h1 class="mt-4">@lang('gzero-core::common.register')</h1>

            <form id="register-account-form" method="POST" role="form" action="{{ route('post.register') }}">
                {{ csrf_field() }}
                <div class="form-group">
                    <label class="control-label" for="email">@choice('gzero-core::common.email', 1)</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}"
                           class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }}"
                           placeholder="@choice('gzero-core::common.email', 1)" required autofocus>
                    @if ($errors->has('email'))
                        <div class="invalid-feedback">{{ $errors->first('email') }}</div>
                    @endif
                </div>
                <div class="form-group">
                    <label class="control-label" for="name">@lang('gzero-core::common.nick_name')</label>
                    <input id="name" type="text" name="name" value="{{old('name')}}"
                           class="form-control{{ $errors->has('name') ? ' is-invalid' : '' }}"
                           placeholder="@lang('gzero-core::common.nick_name')" required>
                    @if($errors->has('name'))
                        <div class="invalid-feedback">{{ $errors->first('name') }}</div>
                    @endif
                </div>
                <div class="form-group">
                    <label class="control-label" for="first_name">@lang('gzero-core::common.first_name')</label>
                    <input id="first_name" type="text" name="first_name" value="{{old('first_name')}}"
                           class="form-control{{ $errors->has('first_name') ? ' is-invalid' : '' }}"
                           placeholder="@lang('gzero-core::common.first_name') (@lang('gzero-core::common.optional'))">
                    @if($errors->has('first_name'))
                        <div class="invalid-feedback">{{ $errors->first('first_name') }}</div>
                    @endif
                </div>
                <div class="form-group">
                    <label class="control-label" for="last_name">@lang('gzero-core::common.last_name')</label>
                    <input id="last_name" type="text" name="last_name" value="{{old('last_name')}}"
                           class="form-control{{ $errors->has('last_name') ? ' is-invalid' : '' }}"
                           placeholder="@lang('gzero-core::common.last_name') (@lang('gzero-core::common.optional'))">
                    @if($errors->has('last_name'))
                        <div class="invalid-feedback">{{ $errors->first('last_name') }}</div>
                    @endif
                </div>

                <div class="form-group">
                    <label class="control-label" for="language_code">
                        @lang('gzero-core::user.choose_preferred_language')
                    </label>
                    <select id="language_code" name="language_code"
                            class="form-control{{ $errors->has('language_code') ? ' is-invalid' : '' }}"
                    >
                        @foreach($languages as $lang)
                            <option value="{{ $lang->code }}"
                                    @if(empty(old('language_code')) ?
                                        $language->code === $lang->code :
                                        old('language_code') === $lang->code)
                                    selected
                                    @endif
                            >
                                @lang('gzero-core::language_names.' . $lang->code)
                            </option>
                        @endforeach
                    </select>
                    @if($errors->first('language_code'))
                        <div class="invalid-feedback">{{ $errors->first('language_code') }}</div>
                    @endif
                </div>

                <div class="form-group">
                    <label class="control-label" for="timezone">
                        @lang('gzero-core::user.choose_preferred_timezone')
                    </label>
                    <select id="timezone" name="timezone"
                            class="form-control{{ $errors->has('timezone') ? ' is-invalid' : '' }}"
                    >
                        <option value="" @if(empty(old('timezone'))) selected @endif>
                            @lang('gzero-core::common.no_option_chosen')
                        </option>
                        @foreach($timezones as $timezone)
                            <option value="{{ $timezone['name'] }}"
                                    @if(old('$timezone') == $timezone['name'])
                                    selected
                                    @endif
                            >
                                {{ ($timezone['offset'] ? 'GMT ' . $timezone['offset'] . ' ' : 'GMT ') . $timezone['name'] }}
                            </option>
                        @endforeach
                    </select>
                    @if($errors->first('timezone'))
                        <div class="invalid-feedback">{{ $errors->first('timezone') }}</div>
                    @endif
                </div>

                <div class="form-group">
                    <label class="control-label" for="password">@lang('gzero-core::common.password')</label>
                    <input id="password" type="password" name="password"
                           class="form-control{{ $errors->has('password') ? ' is-invalid' : '' }}"
                           placeholder="@lang('gzero-core::common.password')" required>
                    @if($errors->has('password'))
                        <div class="invalid-feedback">{{ $errors->first('password') }}</div>
                    @endif
                </div>

                <div class="form-group">
                    <button type="submit" class="btn btn-primary btn-lg btn-block">
                        @lang('gzero-core::common.register')
                    </button>
                </div>
                <input id="accountIntent" type="text" name="accountIntent" hidden>
            </form>
            @if(isProviderLoaded('Gzero\Social\ServiceProvider'))
                @include('gzero-social::includes.socialLogin')
            @endif
        </div>
    </div>
@endsection
