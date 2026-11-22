<div class="mb-3">
    @if($isTerminal)
        <x-depot.status-badge :status="$status" class="mb-2" />
    @endif

    <div class="overflow-auto">
        <ol class="list-unstyled d-flex align-items-center mb-0 gap-1 flex-nowrap">
            @foreach($stepItems as $step)
                <li class="d-flex align-items-center">
                    <span @class([
                        'badge rounded-pill px-3 py-2',
                        'bg-primary' => $step['isCurrent'],
                        'bg-label-primary text-primary' => $step['isComplete'],
                        'bg-label-secondary text-muted' => ! $step['isCurrent'] && ! $step['isComplete'],
                    ])>
                        @if($step['isComplete'])
                            <i class="bx bx-check me-1"></i>
                        @else
                            <span class="me-1">{{ $step['number'] }}</span>
                        @endif
                        {{ $step['label'] }}
                    </span>
                    @if(! $step['isLast'])
                        <i class="bx bx-chevron-right text-muted mx-1"></i>
                    @endif
                </li>
            @endforeach
        </ol>
    </div>
</div>
