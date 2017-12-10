@section('asideLeft')
    @if(!empty($slot->toHtml()) || !empty($blocks))
        <div class="{{ isset($class) ? $class : 'col-sm-4' }}">
            @if(!empty($blocks['sidebarLeft']))
                @foreach($blocks['sidebarLeft'] as $index => $block)
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title">{{$block['title']}}</h4>
                            <p class="card-text">{{$block['body']}}</p>
                        </div>
                    </div>
                @endforeach
            @endif

            {{ $slot }}
        </div>
    @endif
@stop
