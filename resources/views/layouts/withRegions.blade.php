@extends('gzero-core::layouts.master')

@php($hasLeftSidebar = View::hasSection('sidebarLeft') || isset($blocks['sidebarLeft']))
@php($hasRightSidebar = View::hasSection('sidebarRight') || isset($blocks['sidebarRight']))

@if ($hasLeftSidebar && $hasRightSidebar)
    @php($contentClass = 'col-sm-4')
@elseif ($hasLeftSidebar || $hasRightSidebar)
    @php($contentClass = 'col-sm-8')
@endif

@component('gzero-core::sections.sidebarLeft', ['blocks' => $blocks ?? []])
    @yield('sidebarLeft')
@endcomponent

@component('gzero-core::sections.content', ['class' => $contentClass ?? null])
    @yield('content')
@endcomponent

@component('gzero-core::sections.sidebarRight', ['blocks' => $blocks ?? []])
    @yield('sidebarRight')
@endcomponent
