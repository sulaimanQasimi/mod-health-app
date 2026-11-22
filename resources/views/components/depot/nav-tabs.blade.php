@if(count($visibleTabs))
    <div class="card mb-3">
        <div class="card-body py-2">
            <ul class="nav nav-pills flex-wrap gap-1 mb-0">
                @foreach($visibleTabs as $tab)
                    <li class="nav-item">
                        <a
                            href="{{ $tab['url'] }}"
                            @class(['nav-link', 'active' => $tab['active']])
                        >
                            <i class="bx {{ $tab['icon'] }} me-1"></i>
                            <span class="d-none d-sm-inline">{{ $tab['label'] }}</span>
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
@endif
