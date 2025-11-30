<?php

namespace SulaimanQasimi\Router\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class Input extends Component
{
    public string $name;
    public string $type;
    public mixed $value;
    public ?string $placeholder;
    public bool $required;
    public bool $disabled;
    public bool $readonly;
    public ?string $id;
    public array $attributes;
    public ?string $variant;
    public ?string $error;

    /**
     * Create a new component instance.
     */
    public function __construct(
        string $name,
        string $type = 'text',
        mixed $value = null,
        ?string $placeholder = null,
        bool $required = false,
        bool $disabled = false,
        bool $readonly = false,
        ?string $id = null,
        array $attributes = [],
        ?string $variant = null,
        ?string $error = null
    ) {
        $this->name = $name;
        $this->type = $type;
        $this->value = $value;
        $this->placeholder = $placeholder;
        $this->required = $required;
        $this->disabled = $disabled;
        $this->readonly = $readonly;
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
        return view('tailwind-forms::components.input');
    }

    /**
     * Get the CSS classes for the input.
     */
    public function getClasses(): string
    {
        $baseClasses = config('tailwind-forms.default_classes.input', '');
        $variantClasses = $this->getVariantClasses();
        
        return trim($baseClasses . ' ' . $variantClasses);
    }

    /**
     * Get variant-specific classes.
     */
    protected function getVariantClasses(): string
    {
        if (!$this->variant) {
            return $this->error ? config('tailwind-forms.variants.input.error', '') : '';
        }

        return config("tailwind-forms.variants.input.{$this->variant}", '');
    }

    /**
     * Get the old value for the input.
     */
    public function getOldValue(): mixed
    {
        return old($this->name, $this->value);
    }
}
