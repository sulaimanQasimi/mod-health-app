<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Controllers\V1\Concerns\ManagesDepotAccess;
use App\Http\Controllers\V1\Concerns\ProvidesDepotFormData;
use App\Models\DepotRequest;
use App\Models\DepotTransaction;
use App\Support\DepotRolePermissions;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DepotReportController extends Controller
{
    use ManagesDepotAccess;
    use ProvidesDepotFormData;

    public function index(): Response
    {
        $this->authorizeDepotPermission('depot.report.export');

        $options = $this->depotFormOptions();

        return Inertia::render('Depots/Reports/Index', [
            'filterOptions' => [
                'depots' => $options['activeDepots'],
                'pharmacies' => $options['pharmacies'],
                'medicines' => $options['medicines'],
                'tools' => $options['tools'],
                'transactionTypes' => DepotTransaction::types(),
                'transactionStatuses' => DepotTransaction::statuses(),
                'requestStatuses' => DepotRequest::statuses(),
            ],
            'navUrls' => $this->depotNavUrls(),
            'navPermissions' => $this->depotNavPermissions(),
            'urls' => [
                'export' => route('react.depots.reports.export'),
            ],
        ]);
    }

    public function export(Request $request): ?StreamedResponse
    {
        $this->authorizeDepotPermission('depot.report.export');

        if (! $this->isDepotSystemAdmin()) {
            $allowedIds = $this->allowedDepotIdsForAction(DepotRolePermissions::ACTION_REPORT_EXPORT);
            abort_if($allowedIds === [], 403);

            foreach (['depot_id', 'requesting_depot_id', 'source_depot_id'] as $filterKey) {
                if ($request->filled($filterKey) && ! in_array((int) $request->input($filterKey), $allowedIds, true)) {
                    abort(403);
                }
            }

            $request->merge(['_allowed_depot_ids' => $allowedIds]);
        }

        return app(\App\Http\Controllers\DepotReportController::class)->export($request);
    }
}
