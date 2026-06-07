<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Controllers\V1\Concerns\RendersInertiaPage;

class DepotMovementController extends Controller
{
    use RendersInertiaPage;

    public function depotToDepot()
    {
        return $this->renderPage('global.depot.depot_to_depot');
    }

    public function depotToPharmacy()
    {
        return $this->renderPage('global.depot.depot_to_pharmacy');
    }

}
