<?php

namespace App\Http\Controllers\V1\AppointmentSections;

use App\Http\Controllers\Controller;
use App\Http\Controllers\V1\AppointmentSections\Concerns\AuthorizesAppointmentAccess;
use App\Models\Appointment;
use App\Models\Consultation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ConsultationController extends Controller
{
    use AuthorizesAppointmentAccess;

    public function index(Appointment $appointment): JsonResponse
    {
        $this->authorizeAppointmentView($appointment);
        $user = request()->user();

        $items = $appointment->consultations()
            ->latest()
            ->get()
            ->map(fn (Consultation $item) => [
                'id' => $item->id,
                'description' => $item->description,
                'status' => $item->status,
                'date' => $item->date ? verta($item->date)->format('Y-m-d') : null,
            ])
            ->values()
            ->all();

        return $this->sectionIndexResponse($items, $appointment, [
            'create' => ! $appointment->is_completed && $user->can('add-consultations'),
            'edit' => $user->can('edit-consultations'),
            'delete' => $user->can('delete-consultations'),
        ]);
    }

    public function destroy(Appointment $appointment, Consultation $consultation): JsonResponse
    {
        $this->authorizeAppointmentView($appointment);
        abort_unless(request()->user()->can('delete-consultations'), 403);
        abort_unless((int) $consultation->appointment_id === (int) $appointment->id, 404);
        $consultation->delete();

        return response()->json(['success' => true]);
    }
}
