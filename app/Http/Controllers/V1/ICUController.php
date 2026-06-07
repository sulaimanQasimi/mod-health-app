<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Controllers\V1\Concerns\RendersInertiaPage;

class ICUController extends Controller
{
    use RendersInertiaPage;

    public function new()
    {
        return $this->renderPage('global.new_icus');
    }

    public function approved()
    {
        return $this->renderPage('global.approved_icus');
    }

    public function rejected()
    {
        return $this->renderPage('global.rejected_icus');
    }

    public function report()
    {
        return $this->renderPage('global.reports');
    }

}
