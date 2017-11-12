<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    @include('gzero-base::includes.head')
</head>
<body class="@yield('bodyClass')">
<div id="root" class="page">
    <div class="wrapper">
        @include('gzero-base::includes.navbar')
        @yield('header')
        <div id="main-container" class="container">
            <div class="row">
                @yield('asideLeft')
                @section('mainContent')
                    @component('gzero-base::sections.content', ['class'=> 'col-sm-12'])
                        @yield('content')
                    @endcomponent
                @show
                @yield('asideRight')
            </div>
        </div>
    </div>
    <footer id="footer" class="clearfix">
        @include('gzero-base::includes.footer')
    </footer>
</div>
@stack('footer')
</body>
</html>
