<?php

namespace App\Http\Controllers\V1\AppointmentSections;

use App\Http\Controllers\Controller;
use App\Http\Controllers\V1\AppointmentSections\Concerns\AuthorizesAppointmentAccess;
use App\Models\Appointment;
use App\Models\DentistRegistration;
use App\Models\Doctor;
use Hekmatinasser\Verta\Verta;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DentistController extends Controller
{
    use AuthorizesAppointmentAccess;

    public function meta(Appointment $appointment): JsonResponse
    {
        $this->authorizeAppointmentView($appointment);

        if (! request()->user()->can('access-dentist-registrations')) {
            abort(403);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'dentists' => $this->dentistDoctors($appointment)
                    ->map(fn (Doctor $doctor) => [
                        'id' => $doctor->id,
                        'name' => $doctor->name,
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

        if (! $user->can('access-dentist-registrations')) {
            return $this->sectionIndexResponse([], $appointment, ['view' => false]);
        }

        $appointment->load('patient:id,name,last_name');
        $patientName = trim(($appointment->patient?->name ?? '').' '.($appointment->patient?->last_name ?? ''));

        $items = $appointment->dentistRegistrations()
            ->with(['dentist:id,name'])
            ->withCount(['examinations', 'treatments', 'xrays', 'dentalNotes'])
            ->latest()
            ->get()
            ->map(fn (DentistRegistration $item) => [
                'id' => $item->id,
                'ref_no' => $item->ref_no,
                'patient_name' => $patientName ?: null,
                'dentist_name' => $item->dentist?->name,
                'registration_date' => $item->registration_date
                    ? verta($item->registration_date)->format('Y-m-d')
                    : null,
                'status' => $item->status,
                'examinations_count' => $item->examinations_count,
                'treatments_count' => $item->treatments_count,
                'xrays_count' => $item->xrays_count,
                'notes_count' => $item->dental_notes_count,
                'urls' => [
                    'show' => route('dentist-registrations.show', $item->id),
                ],
            ])
            ->values()
            ->all();

        return $this->sectionIndexResponse($items, $appointment, [
            'view' => true,
            'create' => ! $appointment->is_completed && $user->can('access-dentist-registrations'),
        ]);
    }

    public function show(Appointment $appointment, DentistRegistration $dentistRegistration): JsonResponse
    {
        $this->authorizeAppointmentView($appointment);
        abort_unless(
            (int) $dentistRegistration->appointment_id === (int) $appointment->id,
            404,
        );

        if (! request()->user()->can('access-dentist-registrations')) {
            abort(403);
        }

        $dentistRegistration->load([
            'dentist:id,name',
            'appointment.patient:id,name,last_name',
        ]);
        $dentistRegistration->loadCount(['examinations', 'treatments', 'xrays', 'dentalNotes']);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $dentistRegistration->id,
                'ref_no' => $dentistRegistration->ref_no,
                'patient_name' => trim(
                    ($dentistRegistration->appointment?->patient?->name ?? '').' '
                    .($dentistRegistration->appointment?->patient?->last_name ?? ''),
                ) ?: '—',
                'dentist_name' => $dentistRegistration->dentist?->name,
                'registration_date' => $dentistRegistration->registration_date
                    ? verta($dentistRegistration->registration_date)->format('Y-m-d')
                    : null,
                'status' => $dentistRegistration->status,
                'notes' => $dentistRegistration->notes,
                'examinations_count' => $dentistRegistration->examinations_count,
                'treatments_count' => $dentistRegistration->treatments_count,
                'xrays_count' => $dentistRegistration->xrays_count,
                'notes_count' => $dentistRegistration->dental_notes_count,
                'created_at' => $dentistRegistration->created_at
                    ? verta($dentistRegistration->created_at)->format('Y-m-d H:i')
                    : null,
                'urls' => [
                    'show' => route('dentist-registrations.show', $dentistRegistration->id),
                ],
            ],
        ]);
    }

    public function store(Request $request, Appointment $appointment): JsonResponse
    {
        $this->authorizeAppointmentView($appointment);
        abort_if($appointment->is_completed, 403);
        abort_unless($request->user()->can('access-dentist-registrations'), 403);

        $dentistIds = $this->dentistDoctors($appointment)->pluck('id')->all();

        $validated = $request->validate([
            'dentist_id' => ['required', Rule::in($dentistIds)],
            'registration_date' => 'required|string',
            'notes' => 'nullable|string',
        ]);

        $registration = DentistRegistration::create([
            'appointment_id' => $appointment->id,
            'dentist_id' => $validated['dentist_id'],
            'registration_date' => Verta::parse($validated['registration_date'])->datetime(),
            'notes' => $validated['notes'] ?? null,
            'branch_id' => $appointment->branch_id,
        ]);

        return response()->json([
            'success' => true,
            'message' => localize('global.dentist_registration_created_successfully'),
            'data' => ['id' => $registration->id],
        ]);
    }

    /**
     * @return \Illuminate\Support\Collection<int, Doctor>
     */
    private function dentistDoctors(Appointment $appointment)
    {
        return Doctor::query()
            ->where('active_status', true)
            ->where('branch_id', $appointment->branch_id)
            ->where('is_dentist', true)
            ->orderBy('name')
            ->get();
    }
}
