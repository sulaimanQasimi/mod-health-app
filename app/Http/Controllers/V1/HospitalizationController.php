<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Controllers\V1\Concerns\RendersInertiaPage;

class HospitalizationController extends Controller
{
    use RendersInertiaPage;

    public function index()
    {
        return $this->renderPage('global.under_hospitalizations');
    }

    public function discharged()
    {
        return $this->renderPage('global.discharged_hospitalizations');
    }

    public function roomManagement()
    {
        return $this->renderPage('global.room_management');
    }

    public function report()
    {
        return $this->renderPage('global.reports');
    }

}
