<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Controllers\V1\Concerns\RendersInertiaPage;

class AnesthesiaController extends Controller
{
    use RendersInertiaPage;

    public function new()
    {
        return $this->renderPage('global.new_anesthesias');
    }

    public function approved()
    {
        return $this->renderPage('global.approved_anesthesias');
    }

    public function rejected()
    {
        return $this->renderPage('global.rejected_anesthesias');
    }

    public function report()
    {
        return $this->renderPage('global.reports');
    }

}
