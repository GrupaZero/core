@component('gzero-core::layouts._sidebarLeftSection', ['class' => 'col-sm-3', 'blocks' => $blocks['sidebarLeft'] ?? []])
    @slot('aboveBlocks')
        <div class="col-sm-12 my-4">
            <h3>{{ $user->displayName() }}</h3>
            <nav class="nav flex-column">
                <a href="{{routeMl('account')}}"
                   class="nav-link{{ (URL::full() == routeMl('account')) ? ' active' : '' }}"
                >
                    @lang('gzero-core::user.my_account')
                </a>
                <a href="{{routeMl('account.oauth')}}"
                   class="nav-link{{ (URL::full() == routeMl('account.oauth')) ? ' active' :'' }}"
                >
                    @lang('gzero-core::user.oauth')
                </a>
            </nav>
        </div>
    @endslot
@endcomponent
