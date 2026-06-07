<?php

namespace App\Http\Controllers\V1\AppointmentSections;

use App\Http\Controllers\Controller;
use App\Http\Controllers\V1\AppointmentSections\Concerns\AuthorizesAppointmentAccess;
use App\Models\Appointment;
use App\Models\BloodBank;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BloodBankController extends Controller
{
    use AuthorizesAppointmentAccess;

    public function index(Appointment $appointment): JsonResponse
    {
        $this->authorizeAppointmentView($appointment);

        $items = $appointment->bloodBanks()->latest()->get()->map(fn (BloodBank $item) => [
            'id' => $item->id,
            'group' => $item->group,
            'rh' => $item->rh,
            'type' => $item->type,
            'quantity' => $item->quantity,
            'status' => $item->status,
            'urls' => ['show' => route('blood_banks.show', $item->id)],
        ])->values()->all();

        return $this->sectionIndexResponse($items, $appointment, [
            'create' => ! $appointment->is_completed,
            'delete' => true,
        ]);
    }

    public function store(Request $request, Appointment $appointment): JsonResponse
    {
        $this->authorizeAppointmentView($appointment);
        abort_if($appointment->is_completed, 403);

        $validated = $request->validate([
            'group' => 'required|string',
            'rh' => 'required|string',
            'type' => 'required|string',
            'quantity' => 'required|integer|min:1',
        ]);

        BloodBank::create([
            ...$validated,
            'branch_id' => $appointment->branch_id,
            'appointment_id' => $appointment->id,
            'patient_id' => $appointment->patient_id,
            'department_id' => $appointment->department_id,
            'created_by' => $request->user()->id,
        ]);

        return response()->json([
            'success' => true,
            'message' => localize('global.blood_request_created_successfully'),
        ]);
    }

    public function destroy(Appointment $appointment, BloodBank $bloodBank): JsonResponse
    {
        $this->authorizeAppointmentView($appointment);
        abort_unless((int) $bloodBank->appointment_id === (int) $appointment->id, 404);
        $bloodBank->delete();

        return response()->json(['success' => true]);
    }
}
