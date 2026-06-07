<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Controllers\V1\Concerns\RendersInertiaPage;

class DoctorPerformanceReportController extends Controller
{
    use RendersInertiaPage;

    public function performance()
    {
        return $this->renderPage('global.user_performance_report');
    }

}
