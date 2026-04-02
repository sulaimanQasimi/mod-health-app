<?php

namespace App\Http\Controllers;

use App\Models\ProstheticCase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProstheticsReportController extends Controller
{
    public function index(Request $request)
    {
        $branchId = auth()->user()->branch_id;

        $from = $request->get('from');
        $to = $request->get('to');
        $status = $request->get('status');

        $caseQuery = ProstheticCase::query()
            ->with('patient')
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

        return view('pages.prosthetics.reports.index', compact(
            'cases',
            'statusCounts',
            'avgDays',
            'deliveredCount',
            'status',
            'from',
            'to'
        ));
    }
}

