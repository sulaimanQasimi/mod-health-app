<?php

namespace App\Http\Controllers\V1\AppointmentSections;

use App\Http\Controllers\Controller;
use App\Http\Controllers\V1\AppointmentSections\Concerns\AuthorizesAppointmentAccess;
use App\Jobs\SendNewAppointmentNotification;
use App\Models\Appointment;
use App\Models\Department;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReferDepartmentController extends Controller
{
    use AuthorizesAppointmentAccess;

    public function meta(Appointment $appointment): JsonResponse
    {
        $this->authorizeAppointmentView($appointment);
        $user = request()->user();
        abort_unless($this->canRefer($user), 403);

        $appointment->load('patient:id,name,last_name');
        $branchId = $appointment->branch_id ?? $user->branch_id;

        return response()->json([
            'success' => true,
            'data' => [
                'patient_id' => $appointment->patient_id,
                'patient_name' => trim(
                    ($appointment->patient?->name ?? '').' '.($appointment->patient?->last_name ?? '')
                ),
                'branch_id' => $branchId,
                'current_department_id' => $appointment->department_id,
                'clinic_type' => $user->clinic_type,
                'departments' => Department::query()
                    ->when($branchId, fn ($query) => $query->where('branch_id', $branchId))
                    ->orderBy('name')
                    ->get(['id', 'name']),
            ],
        ]);
    }

    public function index(Appointment $appointment): JsonResponse
    {
        $this->authorizeAppointmentView($appointment);
        $user = request()->user();

        return $this->sectionIndexResponse([], $appointment, [
            'create' => $this->canRefer($user) && $this->canMutateAppointment($appointment),
        ], [
            'referral_remarks' => $appointment->refferal_remarks,
            'is_completed' => (bool) $appointment->is_completed,
            'count' => filled($appointment->refferal_remarks) ? 1 : 0,
        ]);
    }

    public function store(Request $request, Appointment $appointment): JsonResponse
    {
        $this->authorizeAppointmentView($appointment);
        abort_unless($this->canRefer($request->user()) && $this->canMutateAppointment($appointment), 403);

        $user = $request->user();

        $rules = [
            'department_id' => 'required|exists:departments,id',
            'refferal_remarks' => 'nullable|string|max:191',
        ];

        if ($user->clinic_type === 'both') {
            $rules['clinic_type'] = 'required|in:hospital,clinic';
        }

        $validated = $request->validate($rules);

        $now = now();

        $appointment->update([
            'is_completed' => 1,
            'refferal_remarks' => $validated['refferal_remarks'] ?? null,
        ]);

        $newAppointmentData = [
            'patient_id' => $appointment->patient_id,
            'department_id' => $validated['department_id'],
            'branch_id' => $appointment->branch_id ?? $user->branch_id,
            'date' => $now->format('Y-m-d'),
            'time' => $now->format('H:i:s'),
            'is_completed' => 0,
        ];

        if ($user->clinic_type && $user->clinic_type !== 'both') {
            $newAppointmentData['clinic_type'] = $user->clinic_type;
        } elseif ($user->clinic_type === 'both') {
            $newAppointmentData['clinic_type'] = $validated['clinic_type'];
        }

        $newAppointment = Appointment::create($newAppointmentData);

        SendNewAppointmentNotification::dispatch($newAppointment->created_by, $newAppointment->id);

        $newAppointment->load('department:id,name');

        $message = localize('global.patient_referred_successfully');
        session()->flash('success', $message);

        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => [
                'referral_remarks' => $appointment->refferal_remarks,
                'new_appointment' => [
                    'id' => $newAppointment->id,
                    'department' => $newAppointment->department?->name,
                    'date' => $newAppointment->date,
                    'time' => $newAppointment->time,
                    'token_url' => url("/appointments/{$newAppointment->id}/printToken"),
                ],
                'redirect_url' => route('appointments.completed'),
            ],
        ]);
    }

    protected function canRefer($user): bool
    {
        return $user && $user->can('refer-to-another-doctor');
    }
}
