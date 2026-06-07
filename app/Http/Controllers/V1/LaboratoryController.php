<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Controllers\V1\Concerns\RendersInertiaPage;

class LaboratoryController extends Controller
{
    use RendersInertiaPage;

    public function pending()
    {
        return $this->renderPage('global.pending_tests');
    }

    public function inProgress()
    {
        return $this->renderPage('global.in_progress_tests');
    }

    public function completed()
    {
        return $this->renderPage('global.completed_tests');
    }

    public function scan()
    {
        return $this->renderPage('global.scan_test');
    }

    public function grouped()
    {
        return $this->renderPage('global.grouped_test_results');
    }

    public function registrationReport()
    {
        return $this->renderPage('global.test_registration_report');
    }

    public function registrationReportDetailed()
    {
        return $this->renderPage('global.test_registration_report_detailed');
    }

}
