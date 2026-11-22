@if($badge)
    <span {{ $attributes->class(['badge', $badgeClass]) }}>{{ $label }}</span>
@else
    {{ $label }}
@endif
