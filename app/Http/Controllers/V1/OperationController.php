<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Controllers\V1\Concerns\RendersInertiaPage;

class OperationController extends Controller
{
    use RendersInertiaPage;

    public function new()
    {
        return $this->renderPage('global.new_operations');
    }

    public function approved()
    {
        return $this->renderPage('global.approved_operations');
    }

    public function reserved()
    {
        return $this->renderPage('global.reserved_operations');
    }

    public function completed()
    {
        return $this->renderPage('global.completed_operations');
    }

    public function report()
    {
        return $this->renderPage('global.reports');
    }

}
