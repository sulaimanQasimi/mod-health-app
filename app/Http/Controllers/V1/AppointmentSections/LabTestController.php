<?php

namespace App\Http\Controllers\V1\AppointmentSections;

use App\Http\Controllers\Controller;
use App\Http\Controllers\V1\AppointmentSections\Concerns\AuthorizesAppointmentAccess;
use App\Models\Appointment;
use App\Models\LabType;
use App\Models\PatientTestRegistration;
use App\Models\PatientTestResult;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LabTestController extends Controller
{
    use AuthorizesAppointmentAccess;

    public function meta(Appointment $appointment): JsonResponse
    {
        $this->authorizeAppointmentView($appointment);

        return response()->json([
            'success' => true,
            'data' => [
                'lab_types' => LabType::query()
                    ->with(['category:id,name', 'directLabTestParameters:id,lab_type_id'])
                    ->orderBy('name')
                    ->get()
                    ->map(fn (LabType $labType) => [
                        'id' => $labType->id,
                        'name' => $labType->name,
                        'category_name' => $labType->category?->name,
                        'parameters_count' => $labType->directLabTestParameters->count(),
                    ])
                    ->values()
                    ->all(),
            ],
        ]);
    }

    public function index(Appointment $appointment): JsonResponse
    {
        $this->authorizeAppointmentView($appointment);
        $user = request()->user();
        $appointment->load('patient:id,name,last_name');

        $patientName = trim(($appointment->patient?->name ?? '').' '.($appointment->patient?->last_name ?? ''));

        $items = $appointment->patientTestRegistrations()
            ->with([
                'labType:id,name,category_id',
                'labType.category:id,name',
                'labType.directLabTestParameters:id,lab_type_id',
                'doctor:id,name',
                'assignedSection:id,name',
                'assignedTo:id,name',
            ])
            ->latest()
            ->get()
            ->map(fn (PatientTestRegistration $item) => [
                'id' => $item->id,
                'ref_no' => $item->ref_no,
                'patient_name' => $patientName,
                'test_name' => $item->labType?->name,
                'category_name' => $item->labType?->category?->name,
                'parameters_count' => $item->labType?->directLabTestParameters?->count() ?? 0,
                'status' => $item->status,
                'priority' => $item->priority,
                'doctor_name' => $item->doctor?->name,
                'section_name' => $item->assignedSection?->name,
                'assigned_to_name' => $item->assignedTo?->name,
                'created_at' => $item->created_at ? verta($item->created_at)->format('Y-m-d H:i') : null,
                'urls' => [
                    'print' => $item->status === 'completed' && $item->ref_no
                        ? url("/laboratory/reports/print/{$item->ref_no}")
                        : null,
                ],
            ])
            ->values()
            ->all();

        return $this->sectionIndexResponse($items, $appointment, [
            'create' => ! $appointment->is_completed && $user->can('register-patient-tests'),
        ]);
    }

    public function show(Appointment $appointment, PatientTestRegistration $registration): JsonResponse
    {
        $this->authorizeAppointmentView($appointment);
        abort_unless(
            $registration->testable_type === Appointment::class
            && (int) $registration->testable_id === (int) $appointment->id,
            404,
        );

        $registration->load([
            'labType.category',
            'labType.directLabTestParameters',
            'doctor:id,name',
            'assignedSection:id,name',
            'assignedTo:id,name',
            'results.parameter',
        ]);

        $parameters = $registration->results
            ->filter(fn ($result) => $result->parameter !== null)
            ->map(fn ($result) => [
                'id' => $result->parameter->id,
                'parameter_name' => $result->parameter->parameter_name,
                'unit' => $result->unit ?? $result->parameter->unit,
                'normal_range' => $result->normal_range ?? $result->parameter->normal_range,
                'result' => $result->result,
            ])
            ->values()
            ->all();

        if (empty($parameters) && $registration->labType?->directLabTestParameters) {
            $parameters = $registration->labType->directLabTestParameters
                ->map(fn ($parameter) => [
                    'id' => $parameter->id,
                    'parameter_name' => $parameter->parameter_name,
                    'unit' => $parameter->unit,
                    'normal_range' => $parameter->normal_range,
                    'result' => null,
                ])
                ->values()
                ->all();
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $registration->id,
                'ref_no' => $registration->ref_no,
                'test_name' => $registration->labType?->name,
                'category_name' => $registration->labType?->category?->name,
                'status' => $registration->status,
                'priority' => $registration->priority,
                'doctor_name' => $registration->doctor?->name,
                'section_name' => $registration->assignedSection?->name,
                'assigned_to_name' => $registration->assignedTo?->name,
                'assigned_at' => $registration->assigned_at
                    ? verta($registration->assigned_at)->format('Y-m-d H:i')
                    : null,
                'notes' => $registration->notes,
                'parameters' => $parameters,
                'urls' => [
                    'print' => $registration->status === 'completed' && $registration->ref_no
                        ? url("/laboratory/reports/print/{$registration->ref_no}")
                        : null,
                ],
            ],
        ]);
    }

    public function store(Request $request, Appointment $appointment): JsonResponse
    {
        $this->authorizeAppointmentView($appointment);
        abort_if($appointment->is_completed, 403);
        abort_unless($request->user()->can('register-patient-tests'), 403);

        $validated = $request->validate([
            'lab_type_ids' => 'required|array|min:1',
            'lab_type_ids.*' => 'integer|exists:lab_types,id',
            'priority' => 'required|in:normal,urgent,stat',
            'notes' => 'nullable|string|max:1000',
        ]);

        $labTypes = LabType::query()
            ->with('directLabTestParameters')
            ->whereIn('id', $validated['lab_type_ids'])
            ->get()
            ->keyBy('id');

        abort_if($labTypes->count() !== count($validated['lab_type_ids']), 422);

        DB::beginTransaction();

        try {
            $maxCategoryId = PatientTestRegistration::max('category_id') ?? 0;
            $newCategoryId = $maxCategoryId + 1;
            $createdIds = [];

            foreach ($validated['lab_type_ids'] as $labTypeId) {
                $ref = DB::table('ref_numbers')->lockForUpdate()->first();
                $newRefNo = $ref->last_ref_no + 1;
                DB::table('ref_numbers')->update(['last_ref_no' => $newRefNo]);

                $labType = $labTypes->get($labTypeId);

                $registration = PatientTestRegistration::create([
                    'patient_id' => $appointment->patient_id,
                    'testable_type' => Appointment::class,
                    'testable_id' => $appointment->id,
                    'registration_date' => now(),
                    'ref_no' => $newRefNo,
                    'lab_type_id' => $labTypeId,
                    'category_id' => $newCategoryId,
                    'status' => 'pending',
                    'doctor_id' => $appointment->doctor_id,
                    'branch_id' => $appointment->branch_id,
                    'priority' => $validated['priority'],
                    'notes' => $validated['notes'] ?? null,
                ]);

                $createdIds[] = $registration->id;

                if ($labType && $labType->directLabTestParameters->count() > 0) {
                    foreach ($labType->directLabTestParameters as $parameter) {
                        PatientTestResult::create([
                            'patient_id' => $appointment->patient_id,
                            'ref_no' => $newRefNo,
                            'lab_parameter_id' => $parameter->id,
                            'unit' => $parameter->unit,
                            'normal_range' => $parameter->normal_range,
                            'result' => null,
                            'test_registration_id' => $registration->id,
                        ]);
                    }
                } else {
                    PatientTestResult::create([
                        'patient_id' => $appointment->patient_id,
                        'ref_no' => $newRefNo,
                        'lab_parameter_id' => null,
                        'result' => null,
                        'test_registration_id' => $registration->id,
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => localize('global.lab_test_registrations_created_successfully'),
                'data' => ['registration_ids' => $createdIds],
            ]);
        } catch (\Throwable $exception) {
            DB::rollBack();
            throw $exception;
        }
    }
}
