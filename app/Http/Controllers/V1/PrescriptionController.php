<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Controllers\V1\Concerns\RendersInertiaPage;

class PrescriptionController extends Controller
{
    use RendersInertiaPage;

    public function scanCode()
    {
        return $this->renderPage('global.scan_prescription');
    }

    public function index()
    {
        return $this->renderPage('global.undelivered_prescriptions');
    }

    public function delivered()
    {
        return $this->renderPage('global.delivered_prescriptions');
    }

    public function report()
    {
        return $this->renderPage('global.reports');
    }

}
