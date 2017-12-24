@section('mainContent')
    <div id="content" class="{{ isset($class) ? $class : 'col-sm-12' }}">
        @yield('contentHeaderRegion')
        {{ $slot }}
        @yield('contentFooterRegion')
    </div>
@stop
