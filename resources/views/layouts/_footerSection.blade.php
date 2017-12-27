@section('footerRegion')
    @if(!empty($slot->toHtml()) || !empty($blocks))
        <div id="footer-region" class="block-region clearfix">
            @if(!empty($blocks))
                <div class="row">
                    @foreach($blocks as $index => $block)
                        {!! $block->getView() !!}
                    @endforeach
                </div>
            @endif

            {{ $slot }}
        </div>
    @endif
@stop
