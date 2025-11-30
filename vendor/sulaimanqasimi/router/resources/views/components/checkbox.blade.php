<input
    type="checkbox"
    name="{{ $name }}"
    id="{{ $id }}"
    value="{{ $value }}"
    @if($isChecked()) checked @endif
    @if($required) required @endif
    @if($disabled) disabled @endif
    class="{{ $getClasses() }}"
    @foreach($attributes as $key => $value) {{ $key }}="{{ $value }}" @endforeach
/>
