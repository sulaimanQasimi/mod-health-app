<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Controllers\V1\Concerns\RendersInertiaPage;

class PhysiotherapyProcedureController extends Controller
{
    use RendersInertiaPage;

    public function index()
    {
        return $this->renderPage('global.all_procedures');
    }

    public function myProcedures()
    {
        return $this->renderPage('global.my_procedures');
    }

}
