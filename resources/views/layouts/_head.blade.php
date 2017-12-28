<?php /* @var $user \Gzero\Core\ViewModels\UserViewModel */ ?>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="description" content="@yield('seoDescription', option('general', 'site_desc'))">
<meta name="version" content="{{ config('gzero.app_version') }}">

<title>@yield('title', option('general', 'site_name'))</title>
@yield('metaData')

<script>
    window.Laravel = @json(['csrfToken' => csrf_token()]);
</script>

@if(option('seo', 'google_tag_manager_id') && env('APP_ENV') === 'production')
    <script>
        dataLayer = [];
        @if(!auth()->guest())
        dataLayer.push({'userId': '{{ $user->id() }}'})
        @endif
        @yield('dataLayer')
    </script>
    <!-- Google Tag Manager -->
    <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
                new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
            j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
            'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
        })(window,document,'script','dataLayer', '{{ option('seo', 'google_tag_manager_id') }}');</script>
    <!-- End Google Tag Manager -->
@endif

@yield('head')

<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/css/bootstrap.min.css"
      integrity="sha384-/Y6pD6FV/Vv2HJnA6t+vslU6fwYXjCFtcEpHbNJ0lyAFsXTsjBbfaDjzALeQsN6M" crossorigin="anonymous">
@if (file_exists(public_path('/css/app.css')))
    <link href="{{ mix('/css/app.css') }}" rel="stylesheet">
@endif
@stack('head')
