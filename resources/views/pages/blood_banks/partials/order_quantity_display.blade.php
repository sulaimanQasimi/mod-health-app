{{-- Expects: $bloodBank (BloodBank). Shows raw ml when quantity looks like volume, else bag count. --}}
@php
    $qparts = $bloodBank->orderQuantityDisplayParts();
@endphp
@if ($qparts['mode'] === 'volume_ml')
    <span dir="ltr">{{ $qparts['ml'] }}</span>
    <span dir="ltr">{{ localize('global.unit_volume_ml') }}</span>
@elseif ($qparts['mode'] === 'units')
    {{ $qparts['units'] }}
@else
    —
@endif
