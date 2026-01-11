<?php

namespace SulaimanQasimi\Router\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class Checkbox extends Component
{
    public string $name;
    public mixed $value;
    public bool $checked;
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
        mixed $value = 1,
        bool $checked = false,
        bool $required = false,
        bool $disabled = false,
        ?string $id = null,
        array $attributes = [],
        ?string $variant = null,
        ?string $error = null
    ) {
        $this->name = $name;
        $this->value = $value;
        $this->checked = $checked;
        $this->required = $required;
        $this->disabled = $disabled;
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
        return view('tailwind-forms::components.checkbox');
    }

    /**
     * Get the CSS classes for the checkbox.
     */
    public function getClasses(): string
    {
        $baseClasses = config('tailwind-forms.default_classes.checkbox', '');
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

        return config("tailwind-forms.variants.checkbox.{$this->variant}", '');
    }

    /**
     * Check if the checkbox should be checked.
     */
    public function isChecked(): bool
    {
        $oldValue = old($this->name);
        
        if ($oldValue !== null) {
            return (bool) $oldValue;
        }
        
        return $this->checked;
    }
}
