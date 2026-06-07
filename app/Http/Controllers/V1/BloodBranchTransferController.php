<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Controllers\V1\Concerns\RendersInertiaPage;

class BloodBranchTransferController extends Controller
{
    use RendersInertiaPage;

    public function index()
    {
        return $this->renderPage('global.blood_branch_transfers');
    }

}
