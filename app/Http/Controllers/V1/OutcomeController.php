<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Controllers\V1\Concerns\RendersInertiaPage;

class OutcomeController extends Controller
{
    use RendersInertiaPage;

    public function index()
    {
        return $this->renderPage('global.stock_outcome');
    }

    public function report()
    {
        return $this->renderPage('global.outcome_reports');
    }

}
