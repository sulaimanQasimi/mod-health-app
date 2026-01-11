<?php

namespace SulaimanQasimi\Router\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class Button extends Component
{
    public string $type;
    public string $variant;
    public bool $disabled;
    public ?string $href;
    public array $attributes;

    /**
     * Create a new component instance.
     */
    public function __construct(
        string $type = 'button',
        string $variant = 'primary',
        bool $disabled = false,
        ?string $href = null,
        array $attributes = []
    ) {
        $this->type = $type;
        $this->variant = $variant;
        $this->disabled = $disabled;
        $this->href = $href;
        $this->attributes = $attributes;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View
    {
        return view('tailwind-forms::components.button');
    }

    /**
     * Get the CSS classes for the button.
     */
    public function getClasses(): string
    {
        $baseClasses = config('tailwind-forms.default_classes.button', '');
        $variantClasses = $this->getVariantClasses();
        
        return trim($baseClasses . ' ' . $variantClasses);
    }

    /**
     * Get variant-specific classes.
     */
    protected function getVariantClasses(): string
    {
        return config("tailwind-forms.variants.button.{$this->variant}", '');
    }

    /**
     * Check if this is a link button.
     */
    public function isLink(): bool
    {
        return !is_null($this->href);
    }
}
