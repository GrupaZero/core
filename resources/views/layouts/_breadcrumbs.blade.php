@if ($breadcrumbs)
    <ol class="breadcrumb" itemscope itemtype="http://schema.org/BreadcrumbList">
        @foreach ($breadcrumbs as $index => $breadcrumb)
            @if ($breadcrumb->url && !$breadcrumb->last)
                @if ($breadcrumb->first)
                    <li class="breadcrumb-item"><a href="{{ $breadcrumb->url }}">{{ $breadcrumb->title }}</a></li>
                @else
                    <li class="breadcrumb-item" itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem">
                        <a itemscope itemtype="http://schema.org/Thing"
                           itemprop="item" href="{{ $breadcrumb->url }}">
                            <span itemprop="name">{{ $breadcrumb->title }}</span>
                        </a>
                        <meta itemprop="position" content="{{$index}}"/>
                    </li>
                @endif
            @else
                <li class="breadcrumb-item active">{{ $breadcrumb->title }}</li>
            @endif
        @endforeach
    </ol>
@endif
