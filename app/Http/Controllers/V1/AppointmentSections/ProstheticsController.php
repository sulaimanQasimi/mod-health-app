<?php

namespace App\Http\Controllers\V1\AppointmentSections;

use App\Http\Controllers\Controller;
use App\Http\Controllers\V1\AppointmentSections\Concerns\AuthorizesAppointmentAccess;
use App\Models\Appointment;
use App\Models\ProstheticCase;
use App\Models\ProstheticReferral;
use App\Services\ProstheticsNumberService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProstheticsController extends Controller
{
    use AuthorizesAppointmentAccess;

    public function index(Appointment $appointment): JsonResponse
    {
        $this->authorizeAppointmentView($appointment);
        $user = request()->user();

        if (! $this->canOpenProsthetics($user)) {
            return $this->sectionIndexResponse([], $appointment, ['view' => false]);
        }

        $branchId = $appointment->branch_id ?? $user->branch_id;

        $referrals = ProstheticReferral::query()
            ->where('patient_id', $appointment->patient_id)
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->orderByDesc('referral_date')
            ->get()
            ->map(fn (ProstheticReferral $referral) => [
                'id' => $referral->id,
                'record_type' => 'referral',
                'number' => $referral->referral_number,
                'status' => $referral->status,
                'date' => $referral->referral_date?->format('Y-m-d'),
                'urls' => [
                    'show' => route('prosthetics.referrals.show', $referral),
                ],
            ]);

        $cases = ProstheticCase::query()
            ->where('patient_id', $appointment->patient_id)
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->orderByDesc('updated_at')
            ->get()
            ->map(fn (ProstheticCase $case) => [
                'id' => $case->id,
                'record_type' => 'case',
                'number' => $case->case_number,
                'status' => $case->status,
                'date' => $case->updated_at?->format('Y-m-d'),
                'urls' => [
                    'show' => route('prosthetics.cases.show', $case),
                ],
            ]);

        $items = $referrals
            ->concat($cases)
            ->sortByDesc('date')
            ->values()
            ->all();

        $canCreate = ! $appointment->is_completed && $this->canOpenProsthetics($user);

        return $this->sectionIndexResponse($items, $appointment, [
            'view' => true,
            'create_referral' => $canCreate,
        ]);
    }

    public function showReferral(Appointment $appointment, ProstheticReferral $referral): JsonResponse
    {
        $this->authorizeAppointmentView($appointment);
        abort_unless($this->canOpenProsthetics(request()->user()), 403);
        abort_unless((int) $referral->patient_id === (int) $appointment->patient_id, 404);

        $branchId = $appointment->branch_id ?? request()->user()->branch_id;
        if ($branchId && (int) $referral->branch_id !== (int) $branchId) {
            abort(404);
        }

        $referral->load('convertedCase:id,case_number');

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $referral->id,
                'record_type' => 'referral',
                'number' => $referral->referral_number,
                'status' => $referral->status,
                'referral_date' => $referral->referral_date?->format('Y-m-d'),
                'reason' => $referral->reason,
                'diagnosis_summary' => $referral->diagnosis_summary,
                'notes' => $referral->notes,
                'converted_case_id' => $referral->converted_case_id,
                'converted_case_number' => $referral->convertedCase?->case_number,
                'urls' => [
                    'show' => route('prosthetics.referrals.show', $referral),
                    'case_show' => $referral->converted_case_id
                        ? route('prosthetics.cases.show', $referral->converted_case_id)
                        : null,
                ],
            ],
        ]);
    }

    public function storeReferral(Request $request, Appointment $appointment): JsonResponse
    {
        $this->authorizeAppointmentView($appointment);
        abort_if($appointment->is_completed, 403);
        abort_unless($this->canOpenProsthetics($request->user()), 403);

        $validated = $request->validate([
            'reason' => 'nullable|string',
            'diagnosis_summary' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $referral = new ProstheticReferral([
            'patient_id' => $appointment->patient_id,
            'referral_date' => now()->toDateString(),
            'reason' => $validated['reason'] ?? null,
            'diagnosis_summary' => $validated['diagnosis_summary'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ]);
        $referral->referral_number = ProstheticsNumberService::nextReferralNumber();
        $referral->branch_id = $appointment->branch_id ?? $request->user()->branch_id;
        $referral->status = 'submitted';
        $referral->created_by = Auth::id();
        $referral->updated_by = Auth::id();
        $referral->save();

        return response()->json([
            'success' => true,
            'message' => __('global.success'),
            'data' => [
                'id' => $referral->id,
                'urls' => [
                    'show' => route('prosthetics.referrals.show', $referral),
                ],
            ],
        ]);
    }

    private function canOpenProsthetics($user): bool
    {
        return $user?->can('show-prosthetics-menu') ?? false;
    }
}
