<?php

namespace App\Http\Controllers\V1\AppointmentSections;

use App\Http\Controllers\Controller;
use App\Http\Controllers\V1\AppointmentSections\Concerns\AuthorizesAppointmentAccess;
use App\Models\Appointment;
use App\Models\Diagnose;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DiagnosisController extends Controller
{
    use AuthorizesAppointmentAccess;

    public function index(Appointment $appointment): JsonResponse
    {
        $this->authorizeAppointmentView($appointment);

        $user = request()->user();
        $items = Diagnose::query()
            ->where('appointment_id', $appointment->id)
            ->with('department:id,name')
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (Diagnose $diagnose) => [
                'id' => $diagnose->id,
                'description' => $diagnose->description,
                'type' => (string) $diagnose->type,
                'department_name' => $diagnose->department?->name,
                'created_at' => $diagnose->created_at
                    ? verta($diagnose->created_at)->format('Y-m-d')
                    : null,
                'vitals' => [
                    'bp' => $diagnose->bp,
                    'pr' => $diagnose->pr,
                    'weight' => $diagnose->weight,
                    't' => $diagnose->t,
                    'spo2' => $diagnose->spo2,
                    'pain' => $diagnose->pain,
                ],
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
        abort_unless($request->user()->can('add-diagnose'), 403);
        abort_if($appointment->is_completed, 403);

        $validated = $request->validate([
            'description' => 'required|string',
            'type' => 'required|in:0,1',
            'bp' => 'nullable|string',
            'pr' => 'nullable|string',
            'weight' => 'nullable|string',
            't' => 'nullable|string',
            'spo2' => 'nullable|string',
            'pain' => 'nullable|string',
        ]);

        $diagnose = Diagnose::create([
            ...$validated,
            'patient_id' => $appointment->patient_id,
            'appointment_id' => $appointment->id,
            'department_id' => $appointment->department_id,
        ]);

        return response()->json([
            'success' => true,
            'message' => localize('global.diagnose_created_successfully.'),
            'data' => $diagnose,
        ]);
    }

    public function update(Request $request, Appointment $appointment, Diagnose $diagnose): JsonResponse
    {
        $this->authorizeAppointmentView($appointment);
        abort_unless($request->user()->can('edit-diagnoses'), 403);
        abort_unless($diagnose->appointment_id === $appointment->id, 404);

        $validated = $request->validate([
            'description' => 'required|string',
            'type' => 'required|in:0,1',
            'bp' => 'nullable|string',
            'pr' => 'nullable|string',
            'weight' => 'nullable|string',
            't' => 'nullable|string',
            'spo2' => 'nullable|string',
            'pain' => 'nullable|string',
        ]);

        $diagnose->update($validated);

        return response()->json([
            'success' => true,
            'message' => localize('global.diagnose_updated_successfully.'),
            'data' => $diagnose,
        ]);
    }

    public function destroy(Appointment $appointment, Diagnose $diagnose): JsonResponse
    {
        $this->authorizeAppointmentView($appointment);
        abort_unless(request()->user()->can('delete-diagnoses'), 403);
        abort_unless($diagnose->appointment_id === $appointment->id, 404);

        $diagnose->delete();

        return response()->json([
            'success' => true,
            'message' => localize('global.diagnose_deleted_successfully.'),
        ]);
    }

    /**
     * @return array<string, bool>
     */
    private function permissions($user, Appointment $appointment): array
    {
        return [
            'create' => ! $appointment->is_completed && $user->can('add-diagnose'),
            'edit' => $user->can('edit-diagnoses'),
            'delete' => $user->can('delete-diagnoses'),
        ];
    }
}
