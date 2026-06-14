<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Controllers\V1\Concerns\ManagesProstheticsAccess;
use App\Http\Controllers\V1\Concerns\PaginatesInertiaIndex;
use App\Models\ProstheticCase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class ProstheticsReportController extends Controller
{
    use ManagesProstheticsAccess;
    use PaginatesInertiaIndex;

    public function index(Request $request): Response
    {
        $this->authorizeProstheticsMenu();

        $branchId = $this->userBranchId();
        $hasSearch = $request->boolean('search')
            || $request->filled('status')
            || ($request->filled('from') && $request->filled('to'));

        $from = $request->get('from');
        $to = $request->get('to');
        $status = $request->get('status');

        if ($request->filled('from') && ! $request->filled('to')) {
            $hasSearch = false;
        }

        $cases = [
            'data' => [],
            'links' => [],
            'meta' => [
                'current_page' => 1,
                'last_page' => 1,
                'per_page' => 20,
                'total' => 0,
                'from' => null,
                'to' => null,
            ],
        ];
        $statusCounts = collect();
        $avgDays = null;
        $deliveredCount = 0;

        if ($hasSearch) {
            $caseQuery = ProstheticCase::query()
                ->with('patient:id,name,last_name')
                ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
                ->when($status, fn ($q) => $q->where('status', $status));

            if ($from && $to) {
                try {
                    $fromDate = verta()->parse($from)->datetime();
                    $toDate = verta()->parse($to)->datetime();
                    $caseQuery->whereDate('prosthetic_cases.created_at', '>=', $fromDate)
                        ->whereDate('prosthetic_cases.created_at', '<=', $toDate);
                } catch (\Throwable) {
                    $hasSearch = false;
                }
            }

            if ($hasSearch) {
                $caseQuery
                    ->with([
                        'deliveries' => fn ($q) => $q->orderByDesc('delivered_at')->take(1),
                    ])
                    ->orderByDesc('created_at');

                $paginator = $this->paginateQuery($caseQuery, $request, 20, [10, 20, 50]);
                $cases = $this->paginationPayload($paginator, fn (ProstheticCase $case) => [
                    'id' => $case->id,
                    'case_number' => $case->case_number,
                    'status' => $case->status,
                    'created_at' => $case->created_at
                        ? verta($case->created_at)->format('Y/m/d')
                        : null,
                    'patient' => $case->patient ? [
                        'name' => $case->patient->name,
                        'last_name' => $case->patient->last_name,
                    ] : null,
                    'deliveries' => $case->deliveries->map(fn ($delivery) => [
                        'delivered_at' => $delivery->delivered_at
                            ? verta($delivery->delivered_at)->format('Y/m/d')
                            : null,
                    ])->values()->all(),
                ]);

                $statusCountsQuery = ProstheticCase::query()
                    ->when($branchId, fn ($q) => $q->where('branch_id', $branchId));

                if ($from && $to) {
                    try {
                        $fromDate = verta()->parse($from)->datetime();
                        $toDate = verta()->parse($to)->datetime();
                        $statusCountsQuery->whereDate('created_at', '>=', $fromDate)
                            ->whereDate('created_at', '<=', $toDate);
                    } catch (\Throwable) {
                        // Keep unfiltered counts when dates are invalid.
                    }
                }

                $statusCounts = $statusCountsQuery
                    ->selectRaw('status, count(*) as c')
                    ->groupBy('status')
                    ->pluck('c', 'status');

                $turnaroundAgg = DB::table('prosthetic_cases as c')
                    ->join('prosthetic_deliveries as d', 'd.prosthetic_case_id', '=', 'c.id')
                    ->when($branchId, fn ($q) => $q->where('c.branch_id', $branchId))
                    ->when($from && $to, function ($q) use ($from, $to) {
                        try {
                            $fromDate = verta()->parse($from)->datetime();
                            $toDate = verta()->parse($to)->datetime();
                            $q->whereDate('c.created_at', '>=', $fromDate)
                                ->whereDate('c.created_at', '<=', $toDate);
                        } catch (\Throwable) {
                            // Skip date filter.
                        }
                    })
                    ->selectRaw('COUNT(*) as delivered_count, AVG(TIMESTAMPDIFF(DAY, c.created_at, d.delivered_at)) as avg_days')
                    ->first();

                $avgDays = $turnaroundAgg?->avg_days !== null ? round((float) $turnaroundAgg->avg_days, 2) : null;
                $deliveredCount = (int) ($turnaroundAgg?->delivered_count ?? 0);
            }
        }

        return Inertia::render('Prosthetics/Reports/Index', [
            'cases' => $cases,
            'statusCounts' => $statusCounts,
            'hasSearch' => $hasSearch,
            'summary' => [
                'avg_days' => $avgDays,
                'delivered_count' => $deliveredCount,
                'total_cases' => $cases['meta']['total'],
            ],
            'filters' => array_merge([
                'status' => '',
                'from' => '',
                'to' => '',
            ], $request->only(['status', 'from', 'to'])),
            'statusOptions' => ProstheticCase::statusList(),
            'urls' => [
                'current' => route('react.prosthetics.reports.index'),
                'dashboard' => route('react.prosthetics.dashboard'),
                'caseShow' => url('/react/prosthetics/cases'),
            ],
        ]);
    }
}
