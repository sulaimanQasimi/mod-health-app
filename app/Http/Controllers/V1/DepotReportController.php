<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Controllers\V1\Concerns\ManagesDepotAccess;
use App\Http\Controllers\V1\Concerns\ProvidesDepotFormData;
use App\Models\DepotRequest;
use App\Models\DepotTransaction;
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
            'urls' => [
                'export' => route('react.depots.reports.export'),
            ],
        ]);
    }

    public function export(Request $request): ?StreamedResponse
    {
        $this->authorizeDepotPermission('depot.report.export');

        return app(\App\Http\Controllers\DepotReportController::class)->export($request);
    }
}
