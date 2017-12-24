@section('asideLeft')
    @if(!empty($slot->toHtml()) || !empty($blocks))
        <aside id="sidebarLeft" class="{{ isset($class) ? $class : 'col-sm-4' }}">
            @if(!empty($blocks))
                @foreach($blocks as $index => $block)
                    {!! $block->view !!}
                @endforeach
            @endif

            {{ $slot }}
        </aside>
    @endif
@stop
