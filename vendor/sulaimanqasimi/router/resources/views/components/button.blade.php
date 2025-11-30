@if($isLink())
    <a
        href="{{ $href }}"
        class="{{ $getClasses() }}"
        @if($disabled) aria-disabled="true" @endif
        @foreach($attributes as $key => $value) {{ $key }}="{{ $value }}" @endforeach
    >
        {{ $slot }}
    </a>
@else
    <button
        type="{{ $type }}"
        @if($disabled) disabled @endif
        class="{{ $getClasses() }}"
        @foreach($attributes as $key => $value) {{ $key }}="{{ $value }}" @endforeach
    >
        {{ $slot }}
    </button>
@endif
