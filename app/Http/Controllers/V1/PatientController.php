<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Controllers\V1\Concerns\RendersInertiaPage;

class PatientController extends Controller
{
    use RendersInertiaPage;

    public function index()
    {
        return $this->renderPage('global.patients_list');
    }

    public function create()
    {
        return $this->renderPage('global.create_patient');
    }

    public function report()
    {
        return $this->renderPage('global.reports');
    }

}
