<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Controllers\ProstheticReferralController as LegacyProstheticReferralController;
use App\Http\Controllers\V1\Concerns\ManagesProstheticsAccess;
use App\Http\Controllers\V1\Concerns\PaginatesInertiaIndex;
use App\Models\Patient;
use App\Models\ProstheticCase;
use App\Models\ProstheticReferral;
use App\Services\ProstheticsNumberService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class ProstheticReferralController extends Controller
{
    use ManagesProstheticsAccess;
    use PaginatesInertiaIndex;

    public function index(Request $request): Response
    {
        $this->authorizeProstheticsMenu();

        $branchId = $this->userBranchId();

        $query = ProstheticReferral::query()
            ->with('patient:id,name,last_name,phone,nid,id_card')
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->orderByDesc('referral_date');

        if ($request->filled('q')) {
            $q = trim((string) $request->q);
            $query->where(function ($w) use ($q) {
                $w->where('referral_number', 'like', '%'.$q.'%')
                    ->orWhereHas('patient', function ($p) use ($q) {
                        $p->where('name', 'like', '%'.$q.'%')
                            ->orWhere('phone', 'like', '%'.$q.'%')
                            ->orWhere('nid', 'like', '%'.$q.'%')
                            ->orWhere('id_card', 'like', '%'.$q.'%');
                    });
            });
        }

        foreach (['referral_number', 'patient_name', 'phone', 'nid', 'id_card', 'status', 'urgency', 'requested_service_type'] as $field) {
            if ($request->filled($field)) {
                $value = trim((string) $request->input($field));
                if (in_array($field, ['patient_name', 'phone', 'nid', 'id_card'], true)) {
                    $patientField = $field === 'patient_name' ? 'name' : $field;
                    $query->whereHas('patient', fn ($p) => $p->where($patientField, 'like', '%'.$value.'%'));
                } elseif ($field === 'referral_number') {
                    $query->where('referral_number', 'like', '%'.$value.'%');
                } elseif ($field === 'requested_service_type') {
                    $query->where('requested_service_type', 'like', '%'.$value.'%');
                } else {
                    $query->where($field, $value);
                }
            }
        }

        if ($request->filled('patient_id')) {
            $query->where('patient_id', (int) $request->patient_id);
        }

        if ($request->filled('from')) {
            $query->whereDate('referral_date', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $query->whereDate('referral_date', '<=', $request->to);
        }

        $paginator = $this->paginateQuery($query, $request, 25, [10, 15, 25, 50]);
        $referrals = $this->paginationPayload(
            $paginator,
            fn (ProstheticReferral $referral) => $this->transformListItem($referral),
        );

        return Inertia::render('Prosthetics/Referrals/Index', [
            'referrals' => $referrals,
            'filters' => array_merge([
                'q' => '',
                'referral_number' => '',
                'patient_id' => '',
                'patient_name' => '',
                'phone' => '',
                'nid' => '',
                'id_card' => '',
                'status' => '',
                'urgency' => '',
                'requested_service_type' => '',
                'from' => '',
                'to' => '',
            ], $request->only([
                'q', 'referral_number', 'patient_id', 'patient_name', 'phone', 'nid', 'id_card',
                'status', 'urgency', 'requested_service_type', 'from', 'to',
            ])),
            'statusOptions' => [
                'drafted', 'submitted', 'received', 'under_review', 'accepted', 'rejected', 'cancelled', 'converted_to_case',
            ],
            'urls' => [
                'current' => route('react.prosthetics.referrals.index'),
                'create' => route('react.prosthetics.referrals.create'),
                'show' => url('/react/prosthetics/referrals'),
            ],
        ]);
    }

    public function create(Request $request): Response
    {
        $this->authorizeProstheticsMenu();

        return Inertia::render('Prosthetics/Referrals/Create', [
            'prefill' => [
                'patient_id' => $request->filled('patient_id') ? (int) $request->patient_id : null,
            ],
            'urls' => [
                'index' => route('react.prosthetics.referrals.index'),
                'store' => route('react.prosthetics.referrals.store'),
                'patientSearch' => route('react.prosthetics.referrals.patients.search'),
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeProstheticsMenu();

        $data = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'referral_date' => 'required|date',
            'referring_facility' => 'nullable|string|max:255',
            'referring_doctor' => 'nullable|string|max:255',
            'reason' => 'nullable|string',
            'diagnosis_summary' => 'nullable|string',
            'urgency' => 'nullable|string|max:64',
            'requested_service_type' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $referral = new ProstheticReferral($data);
        $referral->referral_number = ProstheticsNumberService::nextReferralNumber();
        $referral->branch_id = $this->userBranchId();
        $referral->status = 'submitted';
        $referral->created_by = Auth::id();
        $referral->updated_by = Auth::id();
        $referral->save();

        return redirect()
            ->route('react.prosthetics.referrals.show', $referral)
            ->with('success', __('global.success'));
    }

    public function show(ProstheticReferral $referral): Response
    {
        $this->authorizeReferral($referral);

        return Inertia::render('Prosthetics/Referrals/Show', [
            'referral' => $this->transformDetail($referral),
            'urls' => $this->referralUrls($referral),
        ]);
    }

    public function edit(ProstheticReferral $referral): Response
    {
        $this->authorizeReferral($referral);

        return Inertia::render('Prosthetics/Referrals/Edit', [
            'referral' => [
                'id' => $referral->id,
                'referral_number' => $referral->referral_number,
                'referral_date' => $referral->referral_date?->format('Y-m-d'),
                'status' => $referral->status,
                'reason' => $referral->reason,
                'diagnosis_summary' => $referral->diagnosis_summary,
                'notes' => $referral->notes,
            ],
            'statusOptions' => [
                'drafted', 'submitted', 'received', 'under_review', 'accepted', 'rejected', 'cancelled', 'converted_to_case',
            ],
            'urls' => $this->referralUrls($referral),
        ]);
    }

    public function update(Request $request, ProstheticReferral $referral): RedirectResponse
    {
        $this->authorizeReferral($referral);

        $data = $request->validate([
            'referral_date' => 'required|date',
            'reason' => 'nullable|string',
            'diagnosis_summary' => 'nullable|string',
            'notes' => 'nullable|string',
            'status' => 'nullable|string|max:64',
        ]);

        $referral->fill($data);
        $referral->updated_by = Auth::id();
        $referral->save();

        return redirect()
            ->route('react.prosthetics.referrals.show', $referral)
            ->with('success', __('global.success'));
    }

    public function accept(ProstheticReferral $referral): RedirectResponse
    {
        $this->authorizeReferral($referral);

        return app(LegacyProstheticReferralController::class)->accept($referral);
    }

    public function reject(Request $request, ProstheticReferral $referral): RedirectResponse
    {
        $this->authorizeReferral($referral);

        return app(LegacyProstheticReferralController::class)->reject($request, $referral);
    }

    public function convertToCase(Request $request, ProstheticReferral $referral): RedirectResponse
    {
        $this->authorizeReferral($referral);

        if ($referral->converted_case_id) {
            return redirect()->route('react.prosthetics.cases.show', $referral->converted_case_id);
        }

        $case = DB::transaction(function () use ($referral) {
            $case = new ProstheticCase([
                'patient_id' => $referral->patient_id,
                'referral_id' => $referral->id,
                'branch_id' => $referral->branch_id ?? $this->userBranchId(),
                'case_number' => ProstheticsNumberService::nextCaseNumber(),
                'status' => ProstheticCase::STATUS_REFERRED,
                'primary_diagnosis' => $referral->diagnosis_summary,
                'created_by' => Auth::id(),
                'updated_by' => Auth::id(),
            ]);
            $case->save();

            $referral->converted_case_id = $case->id;
            $referral->status = 'converted_to_case';
            $referral->updated_by = Auth::id();
            $referral->save();

            return $case;
        });

        return redirect()
            ->route('react.prosthetics.cases.show', $case)
            ->with('success', __('global.success'));
    }

    public function searchPatients(Request $request): JsonResponse
    {
        $this->authorizeProstheticsMenu();

        $q = $request->get('q', '');
        if (strlen($q) < 2) {
            return response()->json([]);
        }

        $patients = Patient::query()
            ->where(function ($w) use ($q) {
                $w->where('name', 'like', '%'.$q.'%')
                    ->orWhere('phone', 'like', '%'.$q.'%')
                    ->orWhere('nid', 'like', '%'.$q.'%')
                    ->orWhere('id', $q);
            })
            ->orderBy('name')
            ->limit(20)
            ->get(['id', 'name', 'father_name', 'phone', 'nid']);

        return response()->json($patients);
    }

    /**
     * @return array<string, mixed>
     */
    private function transformListItem(ProstheticReferral $referral): array
    {
        return [
            'id' => $referral->id,
            'referral_number' => $referral->referral_number,
            'status' => $referral->status,
            'referral_date' => $referral->referral_date?->format('Y-m-d'),
            'urgency' => $referral->urgency,
            'requested_service_type' => $referral->requested_service_type,
            'patient_name' => $referral->patient
                ? trim($referral->patient->name.' '.($referral->patient->last_name ?? ''))
                : null,
            'patient_nid' => $referral->patient?->nid,
            'urls' => [
                'show' => route('react.prosthetics.referrals.show', $referral),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function transformDetail(ProstheticReferral $referral): array
    {
        $referral->loadMissing(['patient:id,name,last_name,phone,nid,id_card', 'convertedCase:id,case_number']);

        return [
            'id' => $referral->id,
            'referral_number' => $referral->referral_number,
            'status' => $referral->status,
            'referral_date' => $referral->referral_date?->format('Y-m-d'),
            'referring_facility' => $referral->referring_facility,
            'referring_doctor' => $referral->referring_doctor,
            'reason' => $referral->reason,
            'diagnosis_summary' => $referral->diagnosis_summary,
            'urgency' => $referral->urgency,
            'requested_service_type' => $referral->requested_service_type,
            'notes' => $referral->notes,
            'converted_case_id' => $referral->converted_case_id,
            'patient' => $referral->patient ? [
                'id' => $referral->patient->id,
                'name' => trim($referral->patient->name.' '.$referral->patient->last_name),
                'phone' => $referral->patient->phone,
                'nid' => $referral->patient->nid,
            ] : null,
            'converted_case' => $referral->convertedCase ? [
                'id' => $referral->convertedCase->id,
                'case_number' => $referral->convertedCase->case_number,
            ] : null,
        ];
    }

    /**
     * @return array<string, string>
     */
    private function referralUrls(ProstheticReferral $referral): array
    {
        return [
            'index' => route('react.prosthetics.referrals.index'),
            'show' => route('react.prosthetics.referrals.show', $referral),
            'edit' => route('react.prosthetics.referrals.edit', $referral),
            'update' => route('react.prosthetics.referrals.update', $referral),
            'accept' => route('react.prosthetics.referrals.accept', $referral),
            'reject' => route('react.prosthetics.referrals.reject', $referral),
            'convert' => route('react.prosthetics.referrals.convert', $referral),
            'caseShow' => $referral->converted_case_id
                ? route('react.prosthetics.cases.show', $referral->converted_case_id)
                : '',
        ];
    }
}
