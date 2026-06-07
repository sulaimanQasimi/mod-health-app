<?php

namespace App\Http\Controllers\V1\AppointmentSections;

use App\Http\Controllers\Controller;
use App\Http\Controllers\V1\AppointmentSections\Concerns\AuthorizesAppointmentAccess;
use App\Models\Appointment;
use App\Models\Prescription;
use Illuminate\Http\JsonResponse;

class PrescriptionController extends Controller
{
    use AuthorizesAppointmentAccess;

    public function index(Appointment $appointment): JsonResponse
    {
        $this->authorizeAppointmentView($appointment);
        $user = request()->user();

        $items = $appointment->prescription()
            ->with(['doctor:id,name', 'prescriptionItems'])
            ->latest()
            ->get()
            ->map(fn (Prescription $item) => [
                'id' => $item->id,
                'doctor_name' => $item->doctor?->name,
                'items_count' => $item->prescriptionItems->count(),
                'created_at' => $item->created_at ? verta($item->created_at)->format('Y-m-d H:i') : null,
            ])
            ->values()
            ->all();

        return $this->sectionIndexResponse($items, $appointment, [
            'create' => ! $appointment->is_completed && $user->can('add-prescription'),
            'delete' => $user->can('delete-prescriptions'),
        ]);
    }

    public function destroy(Appointment $appointment, Prescription $prescription): JsonResponse
    {
        $this->authorizeAppointmentView($appointment);
        abort_unless(request()->user()->can('delete-prescriptions'), 403);
        abort_unless((int) $prescription->appointment_id === (int) $appointment->id, 404);
        $prescription->delete();

        return response()->json(['success' => true]);
    }
}
