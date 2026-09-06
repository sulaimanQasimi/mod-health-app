<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Controllers\V1\Concerns\ManagesDepotAccess;
use App\Http\Controllers\V1\Concerns\ProvidesDepotFormData;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DepotMovementController extends Controller
{
    use ManagesDepotAccess;
    use ProvidesDepotFormData;

    public function depotToDepot(Request $request): RedirectResponse
    {
        $fromDepotId = (int) $request->query('from_depot_id', 0);
        $toDepotId = (int) $request->query('to_depot_id', 0);

        if ($fromDepotId) {
            $this->authorizeDepotPermission('depot.request.create', $fromDepotId);
        } else {
            $this->authorizeDepotPermission('depot.request.create');
        }

        return redirect()->route('depots.requests.create', [
            'source_depot_id' => $request->query('from_depot_id'),
            'requesting_depot_id' => $request->query('to_depot_id'),
        ]);
    }

    public function depotToPharmacy(Request $request): RedirectResponse
    {
        $fromDepotId = (int) $request->query('from_depot_id', 0);

        if ($fromDepotId) {
            $this->authorizeDepotPermission('depot.movement.depot_to_pharmacy', $fromDepotId);
        } else {
            $this->authorizeDepotPermission('depot.movement.depot_to_pharmacy');
        }

        return redirect()->route('depots.requests.create', [
            'destination' => 'pharmacy',
            'source_depot_id' => $request->query('from_depot_id'),
            'pharmacy_id' => $request->query('pharmacy_id'),
        ]);
    }
}
