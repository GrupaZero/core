<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    @include('gzero-core::layouts._head')
</head>
<body class="@yield('bodyClass')">
<div id="root">
    <div class="wrapper">
        <header>
            @include('gzero-core::layouts._navbar')
            @yield('headerRegion')
            @yield('header')
        </header>
        @yield('featuredRegion')
        <main id="main-container" role="main" class="@yield('mainClass', 'container')">
            @yield('breadcrumbs')
            <div class="row">
                @yield('asideLeft')
                @section('mainContent')
                    @component('gzero-core::layouts._contentSection', ['class'=> 'col-sm-12'])
                        @yield('content')
                    @endcomponent
                @show
                @yield('asideRight')
            </div>
        </main>
    </div>
    <footer id="footer" class="clearfix">
        @yield('footerRegion')
        @include('gzero-core::layouts._footer')
    </footer>
</div>
@yield('footerScripts')
</body>
</html>
