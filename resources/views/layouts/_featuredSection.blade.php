@section('featuredRegion')
    @if(!empty($slot->toHtml()) || !empty($blocks))
        <section id="featured-region" class="block-region container-fluid clearfix mb-4">
            @if(!empty($blocks))
                <div class="row">
                    @foreach($blocks as $index => $block)
                        {!! $block->view() !!}
                    @endforeach
                </div>
            @endif
            {{ $slot }}
        </section>
    @endif
@stop
