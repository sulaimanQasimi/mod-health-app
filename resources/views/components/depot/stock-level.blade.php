<span {{ $attributes->class(['badge', $badgeClass]) }}>{{ $label }}</span>
@if($showBar)
    <div class="progress mt-1" style="height: 6px; width: 110px;">
        <div class="progress-bar {{ $barClass }}" role="progressbar" style="width: {{ $barPercent }}%"></div>
    </div>
@endif
