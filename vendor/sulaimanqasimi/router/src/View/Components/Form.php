<?php

namespace SulaimanQasimi\Router\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class Form extends Component
{
    public string $method;
    public string $action;
    public bool $hasFiles;
    public array $attributes;

    /**
     * Create a new component instance.
     */
    public function __construct(
        string $method = 'POST',
        string $action = '',
        bool $hasFiles = false,
        array $attributes = []
    ) {
        $this->method = strtoupper($method);
        $this->action = $action;
        $this->hasFiles = $hasFiles;
        $this->attributes = $attributes;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View
    {
        return view('tailwind-forms::components.form');
    }

    /**
     * Get the form method for the form element.
     */
    public function getFormMethod(): string
    {
        return $this->method === 'GET' ? 'GET' : 'POST';
    }

    /**
     * Get the hidden method field if needed.
     */
    public function getHiddenMethodField(): ?string
    {
        return in_array($this->method, ['PUT', 'PATCH', 'DELETE']) ? $this->method : null;
    }
}
