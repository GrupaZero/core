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
@section('footerScripts')
    @if (file_exists(public_path('/js/manifest.js')))
        <script src="{{ mix('/js/manifest.js') }}"></script>
    @endif
    @if (file_exists(public_path('/js/vendor.js')))
        <script src="{{ mix('/js/vendor.js') }}"></script>
    @endif
    @if (file_exists(public_path('/js/app.js')))
        <script src="{{ mix('/js/app.js') }}"></script>
    @else
        <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"
                integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN"
                crossorigin="anonymous"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js"
                integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q"
                crossorigin="anonymous"></script>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"
                integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl"
                crossorigin="anonymous"></script>
    @endif
@show
</body>
</html>
