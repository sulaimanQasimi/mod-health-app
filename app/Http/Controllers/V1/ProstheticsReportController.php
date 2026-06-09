<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Controllers\V1\Concerns\ManagesProstheticsAccess;
use App\Models\ProstheticCase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class ProstheticsReportController extends Controller
{
    use ManagesProstheticsAccess;

    public function index(Request $request): Response
    {
        $this->authorizeProstheticsMenu();

        $branchId = $this->userBranchId();
        $from = $request->get('from');
        $to = $request->get('to');
        $status = $request->get('status');

        $caseQuery = ProstheticCase::query()
            ->with('patient:id,name,last_name')
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->when($status, fn ($q) => $q->where('status', $status));

        if ($from && $to) {
            $caseQuery->whereDate('prosthetic_cases.created_at', '>=', $from)
                ->whereDate('prosthetic_cases.created_at', '<=', $to);
        }

        $cases = $caseQuery
            ->with([
                'deliveries' => fn ($q) => $q->orderByDesc('delivered_at')->take(1),
            ])
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        $statusCounts = ProstheticCase::query()
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->when($from && $to, function ($q) use ($from, $to) {
                $q->whereDate('created_at', '>=', $from)->whereDate('created_at', '<=', $to);
            })
            ->selectRaw('status, count(*) as c')
            ->groupBy('status')
            ->pluck('c', 'status');

        $turnaroundAgg = DB::table('prosthetic_cases as c')
            ->join('prosthetic_deliveries as d', 'd.prosthetic_case_id', '=', 'c.id')
            ->when($branchId, fn ($q) => $q->where('c.branch_id', $branchId))
            ->when($from && $to, function ($q) use ($from, $to) {
                $q->whereDate('c.created_at', '>=', $from)->whereDate('c.created_at', '<=', $to);
            })
            ->selectRaw('COUNT(*) as delivered_count, AVG(TIMESTAMPDIFF(DAY, c.created_at, d.delivered_at)) as avg_days')
            ->first();

        $avgDays = $turnaroundAgg?->avg_days !== null ? round((float) $turnaroundAgg->avg_days, 2) : null;
        $deliveredCount = (int) ($turnaroundAgg?->delivered_count ?? 0);

        return Inertia::render('Prosthetics/Reports/Index', [
            'cases' => $cases,
            'statusCounts' => $statusCounts,
            'summary' => [
                'avg_days' => $avgDays,
                'delivered_count' => $deliveredCount,
                'total_cases' => $cases->total(),
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
