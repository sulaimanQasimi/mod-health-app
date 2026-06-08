<?php

namespace App\Http\Controllers\V1\AppointmentSections;

use App\Http\Controllers\Controller;
use App\Http\Controllers\V1\AppointmentSections\Concerns\AuthorizesAppointmentAccess;
use App\Models\Appointment;
use App\Models\NephrologyRegistration;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NephrologyController extends Controller
{
    use AuthorizesAppointmentAccess;

    public function index(Appointment $appointment): JsonResponse
    {
        $this->authorizeAppointmentView($appointment);
        $user = request()->user();

        if (! $this->canOpenNephrology($user)) {
            return $this->sectionIndexResponse([], $appointment, ['view' => false]);
        }

        $appointment->load('patient:id,name,last_name');
        $patientName = trim(($appointment->patient?->name ?? '').' '.($appointment->patient?->last_name ?? ''));

        $items = $appointment->nephrologyRegistrations()
            ->with(['doctor:id,name', 'disease:id,name'])
            ->latest()
            ->get()
            ->map(fn (NephrologyRegistration $item) => [
                'id' => $item->id,
                'ref_no' => $item->ref_no,
                'patient_name' => $patientName ?: null,
                'doctor_name' => $item->doctor?->name,
                'disease_name' => $item->disease?->name ?? $item->diagnosis,
                'visit_date' => $item->visit_date ? verta($item->visit_date)->format('Y-m-d') : null,
                'status' => $item->status,
                'needs_acceptance' => $item->needsAcceptance(),
                'urls' => [
                    'show' => route('react.nephrology-registrations.show', $item->id),
                ],
            ])
            ->values()
            ->all();

        return $this->sectionIndexResponse($items, $appointment, [
            'view' => true,
            'create' => ! $appointment->is_completed && $this->canOpenNephrology($user),
        ]);
    }

    public function show(Appointment $appointment, NephrologyRegistration $nephrologyRegistration): JsonResponse
    {
        $this->authorizeAppointmentView($appointment);
        abort_unless(
            (int) $nephrologyRegistration->appointment_id === (int) $appointment->id,
            404,
        );

        if (! $this->canOpenNephrology(request()->user())) {
            abort(403);
        }

        $nephrologyRegistration->load([
            'doctor:id,name',
            'disease:id,name',
            'patient:id,name,last_name',
            'appointment.patient:id,name,last_name',
        ]);

        $patient = $nephrologyRegistration->appointment?->patient ?? $nephrologyRegistration->patient;

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $nephrologyRegistration->id,
                'ref_no' => $nephrologyRegistration->ref_no,
                'patient_name' => trim(($patient?->name ?? '').' '.($patient?->last_name ?? '')) ?: null,
                'doctor_name' => $nephrologyRegistration->doctor?->name,
                'disease_name' => $nephrologyRegistration->disease?->name ?? $nephrologyRegistration->diagnosis,
                'visit_date' => $nephrologyRegistration->visit_date
                    ? verta($nephrologyRegistration->visit_date)->format('Y-m-d')
                    : null,
                'status' => $nephrologyRegistration->status,
                'chief_complaint' => $nephrologyRegistration->chief_complaint,
                'ckd_aki_stage' => $nephrologyRegistration->ckd_aki_stage,
                'dialysis_required' => (bool) $nephrologyRegistration->dialysis_required,
                'dialysis_type' => $nephrologyRegistration->dialysis_type,
                'access_type' => $nephrologyRegistration->access_type,
                'notes' => $nephrologyRegistration->notes,
                'follow_up_plan' => $nephrologyRegistration->follow_up_plan,
                'needs_acceptance' => $nephrologyRegistration->needsAcceptance(),
                'created_at' => $nephrologyRegistration->created_at
                    ? verta($nephrologyRegistration->created_at)->format('Y-m-d H:i')
                    : null,
                'urls' => [
                    'show' => route('react.nephrology-registrations.show', $nephrologyRegistration->id),
                    'index' => route('react.nephrology-registrations.index'),
                ],
            ],
        ]);
    }

    public function store(Request $request, Appointment $appointment): JsonResponse
    {
        $this->authorizeAppointmentView($appointment);
        abort_if($appointment->is_completed, 403);
        abort_unless($this->canOpenNephrology($request->user()), 403);

        $validated = $request->validate([
            'notes' => 'nullable|string',
        ]);

        $registration = NephrologyRegistration::create([
            'appointment_id' => $appointment->id,
            'patient_id' => $appointment->patient_id,
            'doctor_id' => null,
            'visit_date' => now(),
            'branch_id' => $appointment->branch_id ?? $request->user()->branch_id,
            'status' => 'pending',
            'notes' => $validated['notes'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => localize('global.nephrology_registration_submitted_successfully'),
            'data' => ['id' => $registration->id],
        ]);
    }

    private function canOpenNephrology($user): bool
    {
        return $user->can('access-nephrology-registrations')
            && optional($user->doctor)->is_nephrologist;
    }
}
