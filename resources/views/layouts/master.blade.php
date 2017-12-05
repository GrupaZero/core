<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    @include('gzero-core::layouts._head')
</head>
<body class="@yield('bodyClass')">
<div id="root" class="page">
    <div class="wrapper">
        @include('gzero-core::layouts._navbar')
        @yield('header')
        <div id="main-container" class="container">
            <div class="row">
                @yield('asideLeft')
                @section('mainContent')
                    @component('gzero-core::layouts._contentSection', ['class'=> 'col-sm-12'])
                        @yield('content')
                    @endcomponent
                @show
                @yield('asideRight')
            </div>
        </div>
    </div>
    <footer id="footer" class="clearfix">
        @include('gzero-core::layouts._footer')
    </footer>
</div>
@stack('footer')
</body>
</html>
