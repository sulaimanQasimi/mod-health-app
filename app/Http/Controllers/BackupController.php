<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\RendersInertiaPage;

class BackupController extends Controller
{
    use RendersInertiaPage;

    public function index()
    {
        return $this->renderPage('global.backups');
    }

}
