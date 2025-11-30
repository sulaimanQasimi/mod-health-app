<textarea
    name="{{ $name }}"
    id="{{ $id }}"
    rows="{{ $rows }}"
    @if($placeholder) placeholder="{{ $placeholder }}" @endif
    @if($required) required @endif
    @if($disabled) disabled @endif
    @if($readonly) readonly @endif
    class="{{ $getClasses() }}"
    @foreach($attributes as $key => $value) {{ $key }}="{{ $value }}" @endforeach
>{{ $getOldValue() }}</textarea>
