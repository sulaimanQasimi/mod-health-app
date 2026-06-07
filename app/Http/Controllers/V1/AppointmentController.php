<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Controllers\V1\Concerns\RendersInertiaPage;

class AppointmentController extends Controller
{
    use RendersInertiaPage;

    public function index()
    {
        return $this->renderPage('global.all_appointments');
    }

    public function departmentReport()
    {
        return $this->renderPage('global.department_report');
    }

    public function department()
    {
        return $this->renderPage('global.department_appointments');
    }

    public function doctor()
    {
        return $this->renderPage('global.ongoing_appointments');
    }

    public function completed()
    {
        return $this->renderPage('global.completed_appointments');
    }

    public function report()
    {
        return $this->renderPage('global.reports');
    }

}
