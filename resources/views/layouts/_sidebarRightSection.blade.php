@section('asideRight')
    @if(!empty($slot->toHtml()) || (isset($aboveBlocks) && !empty($aboveBlocks->toHtml()))  || !empty($blocks))
        <aside id="sidebarRight" class="{{ isset($class) ? $class : 'col-sm-4' }}">
            @if(isset($aboveBlocks))
                {{ $aboveBlocks }}
            @endif

            @if(!empty($blocks))
                @foreach($blocks as $index => $block)
                    {!! $block->view() !!}
                @endforeach
            @endif

            {{ $slot }}
        </aside>
    @endif
@stop
