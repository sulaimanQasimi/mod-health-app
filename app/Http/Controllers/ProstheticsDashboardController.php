<?php

namespace App\Http\Controllers;

use App\Models\ProstheticCase;
use App\Models\ProstheticReferral;
use App\Models\ProstheticWorkOrder;
use Illuminate\Http\Request;

class ProstheticsDashboardController extends Controller
{
    public function index(Request $request)
    {
        $branchId = auth()->user()->branch_id;

        $caseQuery = ProstheticCase::query()
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId));

        $statusCounts = (clone $caseQuery)
            ->selectRaw('status, count(*) as c')
            ->groupBy('status')
            ->pluck('c', 'status');

        $referralPending = ProstheticReferral::query()
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->whereIn('status', ['submitted', 'received', 'under_review'])
            ->count();

        $waitingApproval = (clone $caseQuery)->where('status', ProstheticCase::STATUS_WAITING_APPROVAL)->count();

        $inProduction = (clone $caseQuery)->where('status', ProstheticCase::STATUS_IN_PRODUCTION)->count()
            + (clone $caseQuery)->where('status', ProstheticCase::STATUS_TRIAL_FIT)->count();

        $workOrdersActive = ProstheticWorkOrder::query()
            ->when($branchId, fn ($q) => $q->whereHas('prostheticCase', fn ($c) => $c->where('branch_id', $branchId)))
            ->whereIn('status', ['issued', 'in_progress', 'on_hold', 'waiting_materials'])
            ->count();

        $recentCases = ProstheticCase::query()
            ->with('patient')
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->orderByDesc('updated_at')
            ->limit(12)
            ->get();

        return view('pages.prosthetics.dashboard', compact(
            'statusCounts',
            'referralPending',
            'waitingApproval',
            'inProduction',
            'workOrdersActive',
            'recentCases'
        ));
    }
}
