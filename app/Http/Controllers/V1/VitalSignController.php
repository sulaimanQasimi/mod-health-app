<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Controllers\V1\Concerns\RendersInertiaPage;

class VitalSignController extends Controller
{
    use RendersInertiaPage;

    public function index()
    {
        return $this->renderPage('global.vital_signs');
    }

}
