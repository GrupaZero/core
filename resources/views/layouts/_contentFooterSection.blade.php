@section('contentFooterRegion')
    @if(!empty($slot->toHtml()) || !empty($blocks))
        <section id="content-footer-region" class="block-region clearfix mb-4">
            @if(!empty($blocks))
                <div class="row">
                    @foreach($blocks as $index => $block)
                        {!! $block->getView() !!}
                    @endforeach
                </div>
            @endif

            {{ $slot }}
        </section>
    @endif
@stop
