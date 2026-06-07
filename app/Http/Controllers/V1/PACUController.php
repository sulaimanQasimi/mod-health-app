<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Controllers\V1\Concerns\RendersInertiaPage;

class PACUController extends Controller
{
    use RendersInertiaPage;

    public function index()
    {
        return $this->renderPage('global.new_pacus');
    }

    public function completed()
    {
        return $this->renderPage('global.completed_pacus');
    }

    public function report()
    {
        return $this->renderPage('global.reports');
    }

}
