@extends('gzero-core::layouts.master')

@php($hasLeftSidebar = View::hasSection('sidebarLeft') || isset($blocks['sidebarLeft']))
@php($hasRightSidebar = View::hasSection('sidebarRight') || isset($blocks['sidebarRight']))

@if ($hasLeftSidebar && $hasRightSidebar)
    @php($contentClass = 'col-sm-4')
@elseif ($hasLeftSidebar || $hasRightSidebar)
    @php($contentClass = 'col-sm-8')
@endif

@component('gzero-core::layouts._sidebarLeftSection', ['blocks' => $blocks ?? []])
    @yield('sidebarLeft')
@endcomponent

@component('gzero-core::layouts._contentSection', ['class' => $contentClass ?? null])
    @yield('content')
@endcomponent

@component('gzero-core::layouts._sidebarRightSection', ['blocks' => $blocks ?? []])
    @yield('sidebarRight')
@endcomponent
