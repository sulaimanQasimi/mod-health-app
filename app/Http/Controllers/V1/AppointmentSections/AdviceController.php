<?php

namespace App\Http\Controllers\V1\AppointmentSections;

use App\Http\Controllers\Controller;
use App\Http\Controllers\V1\AppointmentSections\Concerns\AuthorizesAppointmentAccess;
use App\Models\Advice;
use App\Models\Appointment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdviceController extends Controller
{
    use AuthorizesAppointmentAccess;

    public function index(Appointment $appointment): JsonResponse
    {
        $this->authorizeAppointmentView($appointment);

        $user = request()->user();
        $items = Advice::query()
            ->where('appointment_id', $appointment->id)
            ->with('doctor:id,name')
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (Advice $advice) => [
                'id' => $advice->id,
                'description' => $advice->description,
                'doctor_name' => $advice->doctor?->name,
                'created_at' => $advice->created_at
                    ? verta($advice->created_at)->format('Y-m-d H:i')
                    : null,
            ])
            ->values()
            ->all();

        return response()->json([
            'success' => true,
            'data' => [
                'items' => $items,
                'count' => count($items),
                'meta' => $this->appointmentMeta($appointment),
                'permissions' => $this->permissions($user, $appointment),
            ],
        ]);
    }

    public function store(Request $request, Appointment $appointment): JsonResponse
    {
        $this->authorizeAppointmentView($appointment);
        abort_unless($request->user()->can('add-advice'), 403);
        abort_if($appointment->is_completed, 403);

        $validated = $request->validate([
            'description' => 'required|string|max:1000',
        ]);

        $advice = Advice::create([
            'description' => $validated['description'],
            'appointment_id' => $appointment->id,
            'patient_id' => $appointment->patient_id,
            'doctor_id' => $appointment->doctor_id,
        ]);

        $advice->load('doctor:id,name');

        return response()->json([
            'success' => true,
            'message' => localize('global.advice_created_successfully'),
            'data' => [
                'id' => $advice->id,
                'description' => $advice->description,
                'doctor_name' => $advice->doctor?->name,
                'created_at' => $advice->created_at
                    ? verta($advice->created_at)->format('Y-m-d H:i')
                    : null,
            ],
        ]);
    }

    public function update(Request $request, Appointment $appointment, Advice $advice): JsonResponse
    {
        $this->authorizeAppointmentView($appointment);
        abort_unless($request->user()->can('edit-advices'), 403);
        abort_unless($advice->appointment_id === $appointment->id, 404);

        $validated = $request->validate([
            'description' => 'required|string|max:1000',
        ]);

        $advice->update([
            'description' => $validated['description'],
            'doctor_id' => $appointment->doctor_id,
        ]);

        $advice->load('doctor:id,name');

        return response()->json([
            'success' => true,
            'message' => localize('global.advice_updated_successfully'),
            'data' => [
                'id' => $advice->id,
                'description' => $advice->description,
                'doctor_name' => $advice->doctor?->name,
                'created_at' => $advice->created_at
                    ? verta($advice->created_at)->format('Y-m-d H:i')
                    : null,
            ],
        ]);
    }

    public function destroy(Appointment $appointment, Advice $advice): JsonResponse
    {
        $this->authorizeAppointmentView($appointment);
        abort_unless(request()->user()->can('delete-advices'), 403);
        abort_unless($advice->appointment_id === $appointment->id, 404);

        $advice->delete();

        return response()->json([
            'success' => true,
            'message' => localize('global.advice_deleted_successfully'),
        ]);
    }

    /**
     * @return array<string, bool>
     */
    private function permissions($user, Appointment $appointment): array
    {
        return [
            'create' => ! $appointment->is_completed && $user->can('add-advice'),
            'edit' => $user->can('edit-advices'),
            'delete' => $user->can('delete-advices'),
        ];
    }
}
