<?php

namespace SulaimanQasimi\Router\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class Select extends Component
{
    public string $name;
    public array $options;
    public mixed $value;
    public ?string $placeholder;
    public bool $required;
    public bool $disabled;
    public bool $multiple;
    public ?string $id;
    public array $attributes;
    public ?string $variant;
    public ?string $error;

    /**
     * Create a new component instance.
     */
    public function __construct(
        string $name,
        array $options = [],
        mixed $value = null,
        ?string $placeholder = null,
        bool $required = false,
        bool $disabled = false,
        bool $multiple = false,
        ?string $id = null,
        array $attributes = [],
        ?string $variant = null,
        ?string $error = null
    ) {
        $this->name = $name;
        $this->options = $options;
        $this->value = $value;
        $this->placeholder = $placeholder;
        $this->required = $required;
        $this->disabled = $disabled;
        $this->multiple = $multiple;
        $this->id = $id ?? $name;
        $this->attributes = $attributes;
        $this->variant = $variant;
        $this->error = $error;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View
    {
        return view('tailwind-forms::components.select');
    }

    /**
     * Get the CSS classes for the select.
     */
    public function getClasses(): string
    {
        $baseClasses = config('tailwind-forms.default_classes.select', '');
        $variantClasses = $this->getVariantClasses();
        
        return trim($baseClasses . ' ' . $variantClasses);
    }

    /**
     * Get variant-specific classes.
     */
    protected function getVariantClasses(): string
    {
        if (!$this->variant) {
            return $this->error ? 'border-red-300 focus:border-red-500 focus:ring-red-500' : '';
        }

        return config("tailwind-forms.variants.select.{$this->variant}", '');
    }

    /**
     * Get the old value for the select.
     */
    public function getOldValue(): mixed
    {
        return old($this->name, $this->value);
    }

    /**
     * Check if an option is selected.
     */
    public function isSelected(mixed $optionValue): bool
    {
        $currentValue = $this->getOldValue();
        
        if ($this->multiple) {
            return is_array($currentValue) && in_array($optionValue, $currentValue);
        }
        
        return $currentValue == $optionValue;
    }
}
