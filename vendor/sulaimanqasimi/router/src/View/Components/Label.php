<?php

namespace SulaimanQasimi\Router\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class Label extends Component
{
    public ?string $for;
    public bool $required;
    public array $attributes;

    /**
     * Create a new component instance.
     */
    public function __construct(
        ?string $for = null,
        bool $required = false,
        array $attributes = []
    ) {
        $this->for = $for;
        $this->required = $required;
        $this->attributes = $attributes;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View
    {
        return view('tailwind-forms::components.label');
    }

    /**
     * Get the CSS classes for the label.
     */
    public function getClasses(): string
    {
        return config('tailwind-forms.default_classes.label', '');
    }
}
