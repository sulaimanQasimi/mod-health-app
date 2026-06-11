<?php

namespace App\Services;

use App\Models\BloodDonation;
use App\Models\BloodDonor;
use App\Models\BloodSample;
use App\Models\Patient;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class BloodUnitReceiveService
{
    public function receive(Request $request): void
    {
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
            throw ValidationException::withMessages([
                'expires_date' => localize('global.expires_at_must_be_future'),
            ]);
        }

        $validated['expires_at'] = $expiresAt;
        unset($validated['expires_date'], $validated['expires_time']);

        $validated['branch_id'] = $request->user()->branch_id;

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

        if ($patientId) {
            $patientForDonor = Patient::where('id', $patientId)->where('branch_id', $validated['branch_id'])->first();
            if (! $patientForDonor) {
                throw ValidationException::withMessages([
                    'patient_id' => localize('global.invalid_patient_for_branch'),
                ]);
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
    }
}
