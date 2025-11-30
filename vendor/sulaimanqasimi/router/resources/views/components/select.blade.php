<select
    name="{{ $multiple ? $name . '[]' : $name }}"
    id="{{ $id }}"
    @if($required) required @endif
    @if($disabled) disabled @endif
    @if($multiple) multiple @endif
    class="{{ $getClasses() }}"
    @foreach($attributes as $key => $value) {{ $key }}="{{ $value }}" @endforeach
>
    @if($placeholder)
        <option value="" disabled {{ !$getOldValue() ? 'selected' : '' }}>
            {{ $placeholder }}
        </option>
    @endif
    
    @foreach($options as $value => $label)
        <option value="{{ $value }}" {{ $isSelected($value) ? 'selected' : '' }}>
            {{ $label }}
        </option>
    @endforeach
</select>
