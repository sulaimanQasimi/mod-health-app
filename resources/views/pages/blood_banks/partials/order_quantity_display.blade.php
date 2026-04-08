{{-- Expects: $bloodBank (BloodBank). Shows raw ml when quantity looks like volume, else bag count. --}}
@php
    $qparts = $bloodBank->orderQuantityDisplayParts();
@endphp
@if ($qparts['mode'] === 'volume_ml')
    <span dir="ltr">{{ $qparts['ml'] }}</span>
    <span dir="ltr">{{ localize('global.unit_volume_ml') }}</span>
    @if (($qparts['bags'] ?? 0) >= 1)
        <div class="small text-muted mt-1">
            {{ \Illuminate\Support\Facades\Lang::get('global.blood_qty_estimated_bags_line', ['count' => $qparts['bags']], session()->has('language') ? session('language') : 'dr') }}
        </div>
    @endif
@elseif ($qparts['mode'] === 'units')
    {{ $qparts['units'] }}
@else
    —
@endif
