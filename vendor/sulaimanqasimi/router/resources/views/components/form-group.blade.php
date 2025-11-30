<div class="space-y-1" {{ $attributes }}>
    @if($label)
        <x-tailwind-forms::label 
            :for="$name" 
            :required="$required"
        >
            {{ $label }}
        </x-tailwind-forms::label>
    @endif
    
    {{ $slot }}
    
    @if($help)
        <p class="{{ config('tailwind-forms.default_classes.help', '') }}">
            {{ $help }}
        </p>
    @endif
    
    @if($name)
        <x-tailwind-forms::error :name="$name" />
    @endif
</div>
