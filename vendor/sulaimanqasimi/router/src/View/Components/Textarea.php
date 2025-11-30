<?php

namespace SulaimanQasimi\Router\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class Textarea extends Component
{
    public string $name;
    public mixed $value;
    public ?string $placeholder;
    public bool $required;
    public bool $disabled;
    public bool $readonly;
    public ?string $id;
    public int $rows;
    public array $attributes;
    public ?string $variant;
    public ?string $error;

    /**
     * Create a new component instance.
     */
    public function __construct(
        string $name,
        mixed $value = null,
        ?string $placeholder = null,
        bool $required = false,
        bool $disabled = false,
        bool $readonly = false,
        ?string $id = null,
        int $rows = 3,
        array $attributes = [],
        ?string $variant = null,
        ?string $error = null
    ) {
        $this->name = $name;
        $this->value = $value;
        $this->placeholder = $placeholder;
        $this->required = $required;
        $this->disabled = $disabled;
        $this->readonly = $readonly;
        $this->id = $id ?? $name;
        $this->rows = $rows;
        $this->attributes = $attributes;
        $this->variant = $variant;
        $this->error = $error;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View
    {
        return view('tailwind-forms::components.textarea');
    }

    /**
     * Get the CSS classes for the textarea.
     */
    public function getClasses(): string
    {
        $baseClasses = config('tailwind-forms.default_classes.textarea', '');
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

        return config("tailwind-forms.variants.textarea.{$this->variant}", '');
    }

    /**
     * Get the old value for the textarea.
     */
    public function getOldValue(): mixed
    {
        return old($this->name, $this->value);
    }
}
