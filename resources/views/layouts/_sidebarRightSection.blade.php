@section('asideRight')
    @if(!empty($slot->toHtml()) || !empty($blocks))
        <div class="{{ isset($class) ? $class : 'col-sm-4' }}">
            @if(!empty($blocks['sidebarRight']))
                @foreach($blocks['sidebarRight'] as $index => $block)
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
