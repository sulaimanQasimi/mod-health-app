<?php

namespace App\Http\Controllers\V1\Concerns;

use Inertia\Inertia;
use Inertia\Response;

trait RendersInertiaPage
{
    protected function renderPage(string $titleKey, string $component = 'Placeholder'): Response
    {
        return Inertia::render($component, [
            'pageTitleKey' => $titleKey,
        ]);
    }
}
