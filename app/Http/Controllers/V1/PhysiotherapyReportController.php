<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Controllers\V1\Concerns\ManagesPhysiotherapyReport;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PhysiotherapyReportController extends Controller
{
    use ManagesPhysiotherapyReport;

    public function index(Request $request): Response
    {
        $hasSearch = $this->physiotherapyReportHasSearch($request);
        $dateRange = $this->physiotherapyReportDateRange($request);

        $summary = null;
        $detailed = [];
        $byType = [];
        $byPhysiotherapist = [];
        $error = null;

        if ($hasSearch) {
            if ($dateRange === null) {
                $error = 'global.invalid_date_format';
            } else {
                $startDate = $dateRange['start'];
                $endDate = $dateRange['end'];

                $summary = $this->generatePhysiotherapySummaryReport($startDate, $endDate);
                $detailed = $this->generatePhysiotherapyDetailedReport($startDate, $endDate);
                $byType = $this->generatePhysiotherapyByTypeReport($startDate, $endDate);
                $byPhysiotherapist = $this->generatePhysiotherapyByPhysiotherapistReport($startDate, $endDate);
            }
        }

        return Inertia::render('Physiotherapy/Reports/Index', [
            'hasSearch' => $hasSearch && $error === null,
            'error' => $error,
            'filters' => [
                'start_date' => (string) $request->input('start_date', ''),
                'end_date' => (string) $request->input('end_date', ''),
            ],
            'summary' => $summary,
            'detailed' => $detailed,
            'byType' => $byType,
            'byPhysiotherapist' => $byPhysiotherapist,
            'urls' => [
                'current' => route('physiotherapy-reports.index'),
                'export' => route('physiotherapy-reports.export'),
            ],
        ]);
    }
}
