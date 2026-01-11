<?php

namespace SulaimanQasimi\Router\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class Error extends Component
{
    public string $name;
    public ?string $message;
    public array $attributes;

    /**
     * Create a new component instance.
     */
    public function __construct(
        string $name,
        ?string $message = null,
        array $attributes = []
    ) {
        $this->name = $name;
        $this->message = $message;
        $this->attributes = $attributes;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View
    {
        return view('tailwind-forms::components.error');
    }

    /**
     * Get the CSS classes for the error message.
     */
    public function getClasses(): string
    {
        return config('tailwind-forms.default_classes.error', '');
    }

    /**
     * Get the error message.
     */
    public function getMessage(): ?string
    {
        if ($this->message) {
            return $this->message;
        }

        $errors = session('errors');
        if ($errors && $errors->has($this->name)) {
            return $errors->first($this->name);
        }

        return null;
    }

    /**
     * Check if there's an error for this field.
     */
    public function hasError(): bool
    {
        return !is_null($this->getMessage());
    }
}
