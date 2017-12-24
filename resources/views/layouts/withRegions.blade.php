@extends('gzero-core::layouts.master')

@php($hasLeftSidebar = view()->hasSection('sidebarLeft') || isset($blocks['sidebarLeft']))
@php($hasRightSidebar = view()->hasSection('sidebarRight') || isset($blocks['sidebarRight']))

@if ($hasLeftSidebar && $hasRightSidebar)
    @php($contentClass = 'col-sm-4')
@elseif ($hasLeftSidebar || $hasRightSidebar)
    @php($contentClass = 'col-sm-8')
@endif

@component('gzero-core::layouts._headerSection', ['blocks' => $blocks['header'] ?? []])
    @yield('headerRegion')
@endcomponent

@component('gzero-core::layouts._featuredSection', ['blocks' => $blocks['featured'] ?? []])
    @yield('featuredRegion')
@endcomponent

@component('gzero-core::layouts._sidebarLeftSection', ['blocks' => $blocks['sidebarLeft'] ?? []])
    @yield('sidebarLeft')
@endcomponent

@component('gzero-core::layouts._contentHeaderSection', ['blocks' => $blocks['contentHeader'] ?? []])
    @yield('contentHeaderRegion')
@endcomponent

@component('gzero-core::layouts._contentFooterSection', ['blocks' => $blocks['contentFooter'] ?? []])
    @yield('contentFooterRegion')
@endcomponent

@component('gzero-core::layouts._contentSection', ['class' => $contentClass ?? null])
    @yield('content')
@endcomponent

@component('gzero-core::layouts._sidebarRightSection', ['blocks' => $blocks['sidebarRight'] ?? []])
    @yield('sidebarRight')
@endcomponent

@component('gzero-core::layouts._footerSection', ['blocks' => $blocks['footer'] ?? []])
    @yield('footerRegion')
@endcomponent

