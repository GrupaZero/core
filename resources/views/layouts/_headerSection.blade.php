@section('headerRegion')
    @if(!empty($slot->toHtml()) || !empty($blocks))
        <div id="header-region" class="block-region container-fluid clearfix mb-4">
            @if(!empty($blocks))
                <div class="row">
                    @foreach($blocks as $index => $block)
                        {!! $block->view() !!}
                    @endforeach
                </div>
            @endif

            {{ $slot }}
        </div>
    @endif
@stop
