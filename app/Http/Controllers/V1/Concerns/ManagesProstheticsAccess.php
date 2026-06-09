<?php

namespace App\Http\Controllers\V1\Concerns;

use App\Models\ProstheticCase;
use App\Models\ProstheticReferral;
use App\Models\ProstheticWorkOrder;

trait ManagesProstheticsAccess
{
    protected function authorizeProstheticsMenu(): void
    {
        abort_unless(auth()->user()?->can('show-prosthetics-menu'), 403);
    }

    protected function userBranchId(): ?int
    {
        return auth()->user()?->branch_id;
    }

    protected function authorizeReferral(ProstheticReferral $referral): void
    {
        $this->authorizeProstheticsMenu();

        $branchId = $this->userBranchId();
        if ($branchId && (int) $referral->branch_id !== (int) $branchId) {
            abort(403);
        }
    }

    protected function authorizeCase(ProstheticCase $case): void
    {
        $this->authorizeProstheticsMenu();

        $branchId = $this->userBranchId();
        if ($branchId && (int) $case->branch_id !== (int) $branchId) {
            abort(403);
        }
    }

    /**
     * @return array<string, bool>
     */
    protected function caseWorkflowPermissions(ProstheticCase $case, ?ProstheticWorkOrder $activeWorkOrder): array
    {
        $workflowRanks = [
            ProstheticCase::STATUS_NEW => 0,
            ProstheticCase::STATUS_REFERRED => 1,
            ProstheticCase::STATUS_UNDER_ASSESSMENT => 2,
            ProstheticCase::STATUS_MEASUREMENT_COMPLETED => 3,
            ProstheticCase::STATUS_PRESCRIPTION_COMPLETED => 4,
            ProstheticCase::STATUS_WAITING_APPROVAL => 5,
            ProstheticCase::STATUS_APPROVED => 6,
            ProstheticCase::STATUS_IN_PRODUCTION => 7,
            ProstheticCase::STATUS_TRIAL_FIT => 8,
            ProstheticCase::STATUS_DELIVERED => 9,
            ProstheticCase::STATUS_UNDER_FOLLOW_UP => 10,
            ProstheticCase::STATUS_CLOSED => 11,
            ProstheticCase::STATUS_CANCELLED => 12,
        ];

        $caseRank = $workflowRanks[$case->status] ?? -1;
        $isReadOnly = in_array($case->status, [ProstheticCase::STATUS_CLOSED, ProstheticCase::STATUS_CANCELLED], true);

        return [
            'is_read_only' => $isReadOnly,
            'edit_assessment' => ! $isReadOnly && $caseRank <= 2,
            'edit_measurements' => ! $isReadOnly && $caseRank <= 2,
            'edit_prescription' => ! $isReadOnly && $caseRank <= 3,
            'edit_estimate' => ! $isReadOnly && $caseRank <= 4,
            'submit_for_approval' => ! $isReadOnly && $caseRank === 4,
            'approve_case' => ! $isReadOnly && $caseRank === 5,
            'create_work_order' => ! $isReadOnly && $caseRank === 6,
            'update_work_order' => ! $isReadOnly && $caseRank === 7
                && $activeWorkOrder
                && $activeWorkOrder->production_stage !== 'completed',
            'issue_stock' => ! $isReadOnly && $caseRank === 7
                && $activeWorkOrder
                && $activeWorkOrder->production_stage !== 'completed',
            'store_fitting' => ! $isReadOnly && $caseRank === 7,
            'store_delivery' => ! $isReadOnly && $caseRank === 8,
            'store_follow_up' => ! $isReadOnly && $caseRank === 9,
            'close_case' => ! $isReadOnly,
            'manage_attachments' => ! $isReadOnly,
        ];
    }

    protected function canManageCatalog(): bool
    {
        return auth()->user()?->can('manage-prosthetics-catalog') ?? false;
    }

    protected function canManageStock(): bool
    {
        return auth()->user()?->can('manage-prosthetics-stock') ?? false;
    }
}
