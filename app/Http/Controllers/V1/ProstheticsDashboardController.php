<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Controllers\V1\Concerns\ManagesProstheticsAccess;
use App\Models\ProstheticCase;
use App\Models\ProstheticReferral;
use App\Models\ProstheticWorkOrder;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProstheticsDashboardController extends Controller
{
    use ManagesProstheticsAccess;

    public function dashboard(Request $request): Response
    {
        $this->authorizeProstheticsMenu();

        $branchId = $this->userBranchId();

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
            ->with('patient:id,name,last_name')
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->orderByDesc('updated_at')
            ->limit(12)
            ->get()
            ->map(fn (ProstheticCase $case) => [
                'id' => $case->id,
                'case_number' => $case->case_number,
                'status' => $case->status,
                'patient_name' => trim(($case->patient->name ?? '').' '.($case->patient->last_name ?? '')),
                'updated_at' => $case->updated_at?->toIso8601String(),
            ]);

        return Inertia::render('Prosthetics/Dashboard', [
            'stats' => [
                'referral_pending' => $referralPending,
                'waiting_approval' => $waitingApproval,
                'in_production' => $inProduction,
                'work_orders_active' => $workOrdersActive,
            ],
            'statusCounts' => $statusCounts,
            'recentCases' => $recentCases,
            'urls' => [
                'reports' => route('prosthetics.reports.index'),
                'cases' => route('prosthetics.cases.index'),
                'caseShow' => url('/prosthetics/cases'),
            ],
        ]);
    }
}
