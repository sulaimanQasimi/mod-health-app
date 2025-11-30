<?php

namespace SulaimanQasimi\Router\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class FormGroup extends Component
{
    public ?string $label;
    public ?string $name;
    public ?string $help;
    public bool $required;
    public array $attributes;

    /**
     * Create a new component instance.
     */
    public function __construct(
        ?string $label = null,
        ?string $name = null,
        ?string $help = null,
        bool $required = false,
        array $attributes = []
    ) {
        $this->label = $label;
        $this->name = $name;
        $this->help = $help;
        $this->required = $required;
        $this->attributes = $attributes;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View
    {
        return view('tailwind-forms::components.form-group');
    }
}
