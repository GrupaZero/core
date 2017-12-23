@section('asideLeft')
    @if(!empty($slot->toHtml()) || !empty($blocks))
        <aside id="sidebarLeft" class="{{ isset($class) ? $class : 'col-sm-4' }}">
            @foreach($blocks as $index => $block)
                {!! $block->view !!}
            @endforeach

            {{ $slot }}
        </aside>
    @endif
@stop
