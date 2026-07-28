<?php

namespace App\Http\Controllers\V1\AppointmentSections;

use App\Http\Controllers\Controller;
use App\Http\Controllers\V1\AppointmentSections\Concerns\AuthorizesAppointmentAccess;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\OphthalmologyRegistration;
use Hekmatinasser\Verta\Verta;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class OphthalmologyController extends Controller
{
    use AuthorizesAppointmentAccess;

    public function meta(Appointment $appointment): JsonResponse
    {
        $this->authorizeAppointmentView($appointment);
        abort_unless(request()->user()->can('create-ophthalmology-registrations'), 403);

        return response()->json([
            'success' => true,
            'data' => [
                'doctors' => $this->doctors($appointment)->map(fn (Doctor $doctor) => [
                    'id' => $doctor->id,
                    'name' => $doctor->name,
                ])->values()->all(),
            ],
        ]);
    }

    public function index(Appointment $appointment): JsonResponse
    {
        $this->authorizeAppointmentView($appointment);
        $user = request()->user();

        if (! $user->can('access-ophthalmology-registrations')) {
            return $this->sectionIndexResponse([], $appointment, ['view' => false, 'create' => false]);
        }

        $appointment->load('patient:id,name,last_name');
        $patientName = trim(($appointment->patient?->name ?? '').' '.($appointment->patient?->last_name ?? ''));

        $items = $appointment->ophthalmologyRegistrations()
            ->with('examiner:id,name')
            ->latest()
            ->get()
            ->map(fn (OphthalmologyRegistration $item) => [
                'id' => $item->id,
                'ref_no' => $item->ref_no,
                'patient_name' => $patientName ?: null,
                'examiner_name' => $item->examiner?->name,
                'registration_date' => $item->registration_date
                    ? verta($item->registration_date)->format('Y-m-d')
                    : null,
                'status' => $item->status,
                'diagnosis' => $item->diagnosis,
                'tests_count' => collect($item->diagnostic_tests ?? [])
                    ->filter(fn ($test) => is_array($test) ? ($test['selected'] ?? true) : filled($test))
                    ->count(),
                'urls' => [
                    'show' => route('react.ophthalmology-registrations.show', $item),
                ],
            ])->values()->all();

        return $this->sectionIndexResponse($items, $appointment, [
            'view' => true,
            'create' => $this->canMutateAppointment($appointment)
                && $user->can('create-ophthalmology-registrations'),
        ]);
    }

    public function store(Request $request, Appointment $appointment): JsonResponse
    {
        $this->authorizeAppointmentView($appointment);
        $this->assertAppointmentMutable($appointment);
        abort_unless($request->user()->can('create-ophthalmology-registrations'), 403);

        $doctorIds = $this->doctors($appointment)->pluck('id')->all();
        $validated = $request->validate([
            'examiner_id' => ['nullable', Rule::in($doctorIds)],
            'registration_date' => ['required', 'string'],
            'chief_complaint' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
        ]);

        $registration = OphthalmologyRegistration::create([
            'appointment_id' => $appointment->id,
            'examiner_id' => $validated['examiner_id'] ?? $appointment->doctor_id,
            'branch_id' => $appointment->branch_id,
            'registration_date' => Verta::parse($validated['registration_date'])->datetime(),
            'chief_complaint' => $validated['chief_complaint'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => localize('global.ophthalmology_registration_created_successfully'),
            'data' => [
                'id' => $registration->id,
                'url' => route('react.ophthalmology-registrations.show', $registration),
            ],
        ], 201);
    }

    private function doctors(Appointment $appointment)
    {
        return Doctor::query()
            ->where('active_status', true)
            ->where('branch_id', $appointment->branch_id)
            ->orderBy('name')
            ->get();
    }
}
