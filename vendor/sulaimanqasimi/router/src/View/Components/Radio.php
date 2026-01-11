<?php

namespace SulaimanQasimi\Router\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class Radio extends Component
{
    public string $name;
    public mixed $value;
    public mixed $checkedValue;
    public bool $required;
    public bool $disabled;
    public ?string $id;
    public array $attributes;
    public ?string $variant;
    public ?string $error;

    /**
     * Create a new component instance.
     */
    public function __construct(
        string $name,
        mixed $value,
        mixed $checkedValue = null,
        bool $required = false,
        bool $disabled = false,
        ?string $id = null,
        array $attributes = [],
        ?string $variant = null,
        ?string $error = null
    ) {
        $this->name = $name;
        $this->value = $value;
        $this->checkedValue = $checkedValue;
        $this->required = $required;
        $this->disabled = $disabled;
        $this->id = $id ?? $name . '_' . $value;
        $this->attributes = $attributes;
        $this->variant = $variant;
        $this->error = $error;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View
    {
        return view('tailwind-forms::components.radio');
    }

    /**
     * Get the CSS classes for the radio.
     */
    public function getClasses(): string
    {
        $baseClasses = config('tailwind-forms.default_classes.radio', '');
        $variantClasses = $this->getVariantClasses();
        
        return trim($baseClasses . ' ' . $variantClasses);
    }

    /**
     * Get variant-specific classes.
     */
    protected function getVariantClasses(): string
    {
        if (!$this->variant) {
            return $this->error ? 'border-red-300 text-red-600 focus:ring-red-500' : '';
        }

        return config("tailwind-forms.variants.radio.{$this->variant}", '');
    }

    /**
     * Check if the radio should be checked.
     */
    public function isChecked(): bool
    {
        $oldValue = old($this->name, $this->checkedValue);
        return $oldValue == $this->value;
    }
}
