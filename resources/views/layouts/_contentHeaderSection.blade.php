@section('contentHeaderRegion')
    @if(!empty($slot->toHtml()) || !empty($blocks))
        <section id="content-header-region" class="block-region clearfix">
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
