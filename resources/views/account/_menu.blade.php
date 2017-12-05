@component('gzero-core::layouts._sidebarLeftSection', ['class' => 'col-sm-3'])
    <h3>{{ $user->displayName() }}</h3>
    <ul class="nav flex-column" role="navigation">
        <li class="nav-item">
            <a href="{{route('account')}}" class="nav-link">@lang('gzero-core::user.my_account')</a>
        </li>
        <li class="nav-item">
            <a href="{{route('account.oauth')}}" class="nav-link">@lang('gzero-core::user.oauth')</a>
        </li>
    </ul>
@endcomponent
