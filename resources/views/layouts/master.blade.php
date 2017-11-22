<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    @include('gzero-core::includes.head')
</head>
<body class="@yield('bodyClass')">
<div id="root" class="page">
    <div class="wrapper">
        @include('gzero-core::includes.navbar')
        @yield('header')
        <div id="main-container" class="container">
            <div class="row">
                @yield('asideLeft')
                @section('mainContent')
                    @component('gzero-core::sections.content', ['class'=> 'col-sm-12'])
                        @yield('content')
                    @endcomponent
                @show
                @yield('asideRight')
            </div>
        </div>
    </div>
    <footer id="footer" class="clearfix">
        @include('gzero-core::includes.footer')
    </footer>
</div>
@stack('footer')
</body>
</html>
