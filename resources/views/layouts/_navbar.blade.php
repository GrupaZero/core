<nav class="navbar navbar-expand-md navbar-dark bg-dark">
    <a class="navbar-brand" href="{{ route('home') }}" title="{{ config('app.name') }}">
        <img src="{{ asset('/images/logo.png') }}" alt="{{ config('app.name') }}">
    </a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarCollapse"
            aria-controls="navbarCollapse" aria-expanded="false" aria-label="@lang('gzero-core::common.toggle_navigation')">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarCollapse">
        <ul class="navbar-nav">
            <li class="nav-item{{ (URL::full() == route('home')) ? ' active' : '' }}">
                <a class="nav-link" href="{{ route('home') }}">
                    @lang('gzero-core::common.home')
                    @if((URL::full() == route('home')))
                        <span class="sr-only">(@lang('gzero-core::common.current'))</span>
                    @endif
                </a>
            </li>
        </ul>
        <ul class="navbar-nav ml-md-auto">
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" id="languageDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    @lang('gzero-core::language_names.' . app()->getLocale())
                </a>
                <div class="dropdown-menu" aria-labelledby="languageDropdown">
                    @foreach($languages as $language)
                        <a href="{{ routeMl('home', $language->code) }}"
                           class="dropdown-item {{ (app()->getLocale() == $language->code) ? 'active' : '' }}"
                           title="{{$language->code}}">
                           @lang('gzero-core::language_names.' . $language->code)
                        </a>
                    @endforeach
                </div>
            </li>
            @guest
                <li class="nav-item ml-4">
                    <a href="{{ route('login') }}" class="btn btn-outline-success my-2 my-sm-0"
                       title="@lang('gzero-core::common.login')">
                        @lang('gzero-core::common.login')
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('register') }}" class="btn btn-outline-primary ml-2 my-2 my-sm-0"
                       title="@lang('gzero-core::common.register')">
                        @lang('gzero-core::common.register')
                    </a>
                </li>
            @endguest
            @auth
            <li class="nav-item dropdown ml-4">
                <a class="nav-link dropdown-toggle" href="#"
                   id="navbarUserNav" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    {{ $user->displayName() }}
                </a>
                <div class="dropdown-menu user-nav dropdown-menu-right" aria-labelledby="navbarUserNav">
                    @if ($user->isSuperAdmin() && isProviderLoaded('Gzero\Admin\ServiceProvider'))
                        <a href="{{ route('admin') }}" target="_blank" class="dropdown-item">
                            @lang('gzero-core::user.admin_panel')
                        </a>
                    @endif
                    <a href="{{ route('account') }}" class="dropdown-item">
                        @lang('gzero-core::user.my_account')
                    </a>
                    <a href="{{ route('account.edit') }}" class="dropdown-item">
                        @lang('gzero-core::user.edit_account')
                    </a>
                    <div class="dropdown-divider"></div>
                    <a href="{{ route('logout') }}" class="dropdown-item">
                        @lang('gzero-core::common.logout')
                    </a>
                </div>
            </li>
            @endauth
        </ul>
    </div>
</nav>
