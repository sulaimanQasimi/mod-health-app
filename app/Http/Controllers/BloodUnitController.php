<?php

namespace App\Http\Controllers;

use App\Models\BloodDonation;
use App\Models\BloodDonor;
use App\Models\BloodSample;
use App\Models\BloodUnitTest;
use App\Models\BloodUnit;
use App\Models\Department;
use App\Models\Patient;
use App\Services\BloodBankStockService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class BloodUnitController extends Controller
{
    public function index(Request $request)
    {
        $branchId = auth()->user()->branch_id;
        $expiredArchivedCount = app(BloodBankStockService::class)->archiveExpiredUnits($branchId, auth()->id());

        $query = BloodUnit::where('branch_id', $branchId)
            ->with(['donation.donor.department', 'donation.donor.patient']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('blood_group')) {
            $query->where('blood_group', $request->blood_group);
        }

        if ($request->filled('rh')) {
            $query->where('rh', $request->rh);
        }

        if ($request->filled('component_type')) {
            $query->where('component_type', $request->component_type);
        }

        if ($request->filled('q')) {
            $q = trim($request->q);
            $query->where('bag_number', 'like', '%'.$q.'%');
        }

        if ($request->filled('expires_within')) {
            $days = max(1, min(90, (int) $request->expires_within));
            $query->where('status', 'available')
                ->where('expires_at', '>', now())
                ->where('expires_at', '<=', now()->addDays($days));
        }

        $sort = $request->input('sort', 'created_at');
        if ($sort === 'expires_at') {
            $query->orderBy('expires_at');
        } else {
            $query->orderByDesc('created_at');
        }

        $units = $query->paginate(30)->withQueryString();

        $departments = Department::where('branch_id', $branchId)->orderBy('name')->get();
        $patientsForDonor = Patient::where('branch_id', $branchId)
            ->orderBy('name')
            ->orderBy('last_name')
            ->limit(500)
            ->get(['id', 'name', 'last_name', 'phone', 'nid']);

        return view('pages.blood_banks.inventory', compact('units', 'departments', 'patientsForDonor', 'expiredArchivedCount'));
    }

    public function show(BloodUnit $bloodUnit)
    {
        $this->ensureBranchBloodUnit($bloodUnit);

        $bloodUnit->load([
            'branch',
            'stockMovements.user',
            'test.testedBy',
            'tests.testedBy',
            'donation.donor.department',
            'donation.donor.patient',
            'donation.samples',
        ]);

        return view('pages.blood_banks.unit_show', compact('bloodUnit'));
    }

    public function store(Request $request)
    {
        $user = $request->user();
        if (! $user->can('receive-blood-units') && ! $user->can('manage-blood-inventory')) {
            abort(403);
        }

        if ($request->input('expires_time') === '') {
            $request->merge(['expires_time' => null]);
        }
        if ($request->input('patient_id') === '' || $request->input('patient_id') === null) {
            $request->merge(['patient_id' => null]);
        }
        if ($request->input('department_id') === '' || $request->input('department_id') === null) {
            $request->merge(['department_id' => null]);
        }

        $validated = $request->validate([
            'blood_group' => 'required|string|in:A,B,AB,O',
            'rh' => 'required|string|in:+,-',
            'component_type' => 'required|string|in:Fresh,RBC,PRBC,Platelets,Plasma,Whole Blood',
            'bag_number' => 'required|string|max:255|unique:blood_units,bag_number',
            'volume_ml' => 'nullable|integer|min:1',
            'collected_at' => 'nullable|date',
            'expires_date' => 'required|date',
            'expires_time' => 'nullable|date_format:H:i',
            'notes' => 'nullable|string|max:2000',
            'donor_name' => 'nullable|string|max:255',
            'donor_father_name' => 'nullable|string|max:255',
            'donor_age' => 'nullable|integer|min:0|max:130',
            'donor_gender' => ['nullable', Rule::in(['male', 'female'])],
            'donor_blood_pressure' => 'nullable|string|max:50',
            'donor_comorbidities' => 'nullable|string|max:5000',
            'donor_type' => ['nullable', Rule::in(['civilian', 'military'])],
            'donor_receiver' => 'nullable|string|max:255',
            'donor_military_department' => 'nullable|string|max:255|required_if:donor_type,military',
            'donor_phone' => 'nullable|string|max:50',
            'donor_national_id' => 'nullable|string|max:50',
            'phlebotomy_at' => 'nullable|date',
            'patient_id' => 'nullable|integer|exists:patients,id',
            'department_id' => 'nullable|integer|exists:departments,id',
            'donor_record_department' => 'nullable|boolean',
        ], [
            'expires_date.required' => localize('global.expires_date_required'),
            'expires_time.date_format' => localize('global.expires_time_invalid'),
            'donor_military_department.required_if' => localize('global.military_department_required_for_military_donor'),
        ]);

        $time = $validated['expires_time'] ?? '23:59';
        if ($time === '' || $time === null) {
            $time = '23:59';
        }
        $expiresAt = Carbon::parse($validated['expires_date'].' '.$time.':00', config('app.timezone'));

        if ($expiresAt->lte(now())) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['expires_date' => localize('global.expires_at_must_be_future')]);
        }

        $validated['expires_at'] = $expiresAt;
        unset($validated['expires_date'], $validated['expires_time']);

        $validated['branch_id'] = auth()->user()->branch_id;

        $donorName = trim((string) ($validated['donor_name'] ?? ''));
        $donorFatherName = trim((string) ($validated['donor_father_name'] ?? ''));
        $donorAge = isset($validated['donor_age']) ? (int) $validated['donor_age'] : null;
        $donorGender = $validated['donor_gender'] ?? null;
        $donorBloodPressure = $validated['donor_blood_pressure'] ?? null;
        $donorComorbidities = $validated['donor_comorbidities'] ?? null;
        $donorType = $validated['donor_type'] ?? 'civilian';
        $donorReceiver = $validated['donor_receiver'] ?? null;
        $donorMilitaryDepartment = $validated['donor_military_department'] ?? null;
        $donorPhone = $validated['donor_phone'] ?? null;
        $donorNationalId = $validated['donor_national_id'] ?? null;
        $phlebotomyAt = $validated['phlebotomy_at'] ?? null;
        $patientId = isset($validated['patient_id']) ? (int) $validated['patient_id'] : null;
        $recordDepartment = $request->boolean('donor_record_department');
        $departmentId = null;
        if (! $patientId && $recordDepartment) {
            $departmentId = $validated['department_id'] ?? null;
        }

        unset(
            $validated['donor_name'],
            $validated['donor_father_name'],
            $validated['donor_age'],
            $validated['donor_gender'],
            $validated['donor_blood_pressure'],
            $validated['donor_comorbidities'],
            $validated['donor_type'],
            $validated['donor_receiver'],
            $validated['donor_military_department'],
            $validated['donor_phone'],
            $validated['donor_national_id'],
            $validated['phlebotomy_at'],
            $validated['patient_id'],
            $validated['department_id'],
            $validated['donor_record_department'],
        );

        $patientForDonor = null;
        if ($patientId) {
            $patientForDonor = Patient::where('id', $patientId)->where('branch_id', $validated['branch_id'])->first();
            if (! $patientForDonor) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['patient_id' => localize('global.invalid_patient_for_branch')]);
            }
            if ($donorName === '') {
                $donorName = trim($patientForDonor->name.' '.($patientForDonor->last_name ?? ''));
            }
        }

        $departmentIdForDonor = $patientId ? null : $departmentId;

        DB::transaction(function () use ($validated, $donorName, $donorFatherName, $donorAge, $donorGender, $donorBloodPressure, $donorComorbidities, $donorType, $donorReceiver, $donorMilitaryDepartment, $donorPhone, $donorNationalId, $phlebotomyAt, $patientId, $departmentIdForDonor) {
            $donationId = null;

            if ($donorName !== '' || $patientId) {
                $donor = null;
                if ($donorNationalId) {
                    $donor = BloodDonor::where('national_id', $donorNationalId)->first();
                }

                if (! $donor) {
                    $donor = BloodDonor::create([
                        'name' => $donorName !== '' ? $donorName : '—',
                        'father_name' => $donorFatherName !== '' ? $donorFatherName : null,
                        'age' => $donorAge,
                        'gender' => $donorGender,
                        'blood_pressure' => $donorBloodPressure,
                        'comorbidities' => $donorComorbidities,
                        'donor_type' => $donorType,
                        'receiver' => $donorReceiver,
                        'military_department' => $donorType === 'military' ? $donorMilitaryDepartment : null,
                        'phone' => $donorPhone,
                        'national_id' => $donorNationalId,
                        'patient_id' => $patientId,
                        'department_id' => $departmentIdForDonor,
                    ]);
                } else {
                    if ($donorName !== '') {
                        $donor->name = $donorName;
                    }
                    if ($donorPhone !== null && $donorPhone !== '') {
                        $donor->phone = $donorPhone;
                    }
                    $donor->father_name = $donorFatherName !== '' ? $donorFatherName : null;
                    $donor->age = $donorAge;
                    $donor->gender = $donorGender;
                    $donor->blood_pressure = $donorBloodPressure;
                    $donor->comorbidities = $donorComorbidities;
                    $donor->donor_type = $donorType;
                    $donor->receiver = $donorReceiver;
                    $donor->military_department = $donorType === 'military' ? $donorMilitaryDepartment : null;
                    $donor->patient_id = $patientId;
                    $donor->department_id = $departmentIdForDonor;
                    $donor->save();
                }

                $phlebotomy = $phlebotomyAt
                    ? Carbon::parse($phlebotomyAt, config('app.timezone'))
                    : ($validated['collected_at']
                        ? Carbon::parse($validated['collected_at'], config('app.timezone'))
                        : now());

                $donation = BloodDonation::create([
                    'branch_id' => $validated['branch_id'],
                    'blood_donor_id' => $donor->id,
                    'phlebotomy_at' => $phlebotomy,
                    'donor_blood_group' => $validated['blood_group'],
                    'donor_rh' => $validated['rh'],
                    'notes' => $validated['notes'] ?? null,
                ]);

                BloodSample::create([
                    'blood_donation_id' => $donation->id,
                    'status' => 'collected',
                ]);

                $donationId = $donation->id;
            }

            $payload = array_merge($validated, ['donation_id' => $donationId]);
            app(BloodBankStockService::class)->receiveUnit($payload);
        });

        return redirect()->back()->with('success', localize('global.blood_unit_received_success'));
    }

    public function saveTests(Request $request, BloodUnit $bloodUnit)
    {
        $this->ensureBranchBloodUnit($bloodUnit);

        $user = $request->user();
        if (! $user->can('manage-blood-inventory') && ! $user->can('receive-blood-units')) {
            abort(403);
        }

        $validated = $request->validate([
            'abo_result' => ['nullable', 'string', Rule::in(['A', 'B', 'AB', 'O'])],
            'rh_result' => ['nullable', 'string', Rule::in(['+', '-'])],
            'dct_result' => ['required', Rule::in(BloodUnitTest::RESULT_VALUES)],
            'ict_result' => ['required', Rule::in(BloodUnitTest::RESULT_VALUES)],
            'hbs_result' => ['required', Rule::in(BloodUnitTest::RESULT_VALUES)],
            'hcv_result' => ['required', Rule::in(BloodUnitTest::RESULT_VALUES)],
            'hiv_result' => ['required', Rule::in(BloodUnitTest::RESULT_VALUES)],
            'vdrl_result' => ['required', Rule::in(BloodUnitTest::RESULT_VALUES)],
            'remarks' => ['nullable', 'string', 'max:5000'],
        ]);

        $overall = $this->computeOverallTestStatus($validated);

        BloodUnitTest::updateOrCreate(
            ['blood_unit_id' => $bloodUnit->id],
            array_merge($validated, [
                'overall_status' => $overall,
                'tested_at' => now(),
                'tested_by' => auth()->id(),
            ])
        );

        return redirect()
            ->back()
            ->with('success', localize('global.blood_unit_tests_saved'));
    }

    public function approveAfterTests(Request $request, BloodUnit $bloodUnit)
    {
        $this->ensureBranchBloodUnit($bloodUnit);

        $user = $request->user();
        if (! $user->can('manage-blood-inventory') && ! $user->can('receive-blood-units')) {
            abort(403);
        }

        $bloodUnit->load('test');
        if (! $bloodUnit->test || $bloodUnit->test->overall_status !== 'passed') {
            return redirect()->back()->with('error', localize('global.blood_unit_tests_must_pass_before_release'));
        }

        // If unit is in quarantine, release it to available
        if ($bloodUnit->status === 'quarantine') {
            try {
                app(BloodBankStockService::class)->setQuarantine($bloodUnit, false, localize('global.blood_release_after_tests'));
            } catch (\Throwable $e) {
                return redirect()->back()->with('error', $e->getMessage());
            }
        }

        return redirect()->back()->with('success', localize('global.blood_unit_released_after_tests'));
    }

    protected function computeOverallTestStatus(array $validated): string
    {
        $keys = ['dct_result', 'ict_result', 'hbs_result', 'hcv_result', 'hiv_result', 'vdrl_result'];

        foreach ($keys as $k) {
            if (($validated[$k] ?? 'pending') === 'positive') {
                return 'failed';
            }
            if (($validated[$k] ?? 'pending') === 'inconclusive' || ($validated[$k] ?? 'pending') === 'pending') {
                return 'pending';
            }
        }

        // All are negative
        return 'passed';
    }

    public function discard(Request $request, BloodUnit $bloodUnit)
    {
        $this->ensureBranchBloodUnit($bloodUnit);

        $user = $request->user();
        if (! $user->can('receive-blood-units') && ! $user->can('manage-blood-inventory')) {
            abort(403);
        }

        $request->validate([
            'reason' => 'nullable|string|max:2000',
        ]);

        try {
            app(BloodBankStockService::class)->discardUnit($bloodUnit, $request->input('reason'));
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('blood_banks.inventory')
            ->with('success', localize('global.blood_unit_discarded_success'));
    }

    public function quarantine(Request $request, BloodUnit $bloodUnit)
    {
        $this->ensureBranchBloodUnit($bloodUnit);

        $user = $request->user();
        if (! $user->can('receive-blood-units') && ! $user->can('manage-blood-inventory')) {
            abort(403);
        }

        $request->validate(['reason' => 'nullable|string|max:2000']);

        try {
            app(BloodBankStockService::class)->setQuarantine($bloodUnit, true, $request->input('reason'));
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        return redirect()->back()->with('success', localize('global.blood_unit_quarantine_set'));
    }

    public function releaseQuarantine(Request $request, BloodUnit $bloodUnit)
    {
        $this->ensureBranchBloodUnit($bloodUnit);

        $user = $request->user();
        if (! $user->can('receive-blood-units') && ! $user->can('manage-blood-inventory')) {
            abort(403);
        }

        $request->validate(['reason' => 'nullable|string|max:2000']);

        try {
            app(BloodBankStockService::class)->setQuarantine($bloodUnit, false, $request->input('reason'));
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        return redirect()->back()->with('success', localize('global.blood_unit_quarantine_released'));
    }

    protected function ensureBranchBloodUnit(BloodUnit $unit): void
    {
        if ((int) $unit->branch_id !== (int) auth()->user()->branch_id) {
            abort(404);
        }
    }
}
