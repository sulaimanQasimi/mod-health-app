<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Controllers\V1\Concerns\RendersInertiaPage;

class ProstheticsDashboardController extends Controller
{
    use RendersInertiaPage;

    public function dashboard()
    {
        return $this->renderPage('global.prosthetics_dashboard');
    }

}
