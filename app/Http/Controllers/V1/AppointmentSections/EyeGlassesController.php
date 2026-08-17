<?php

namespace App\Http\Controllers\V1\AppointmentSections;

use App\Http\Controllers\Controller;
use App\Http\Controllers\V1\AppointmentSections\Concerns\AuthorizesAppointmentAccess;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\EyeGlassesOrder;
use App\Models\OphthalmologyRegistration;
use Hekmatinasser\Verta\Verta;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EyeGlassesController extends Controller
{
    use AuthorizesAppointmentAccess;

    public function meta(Appointment $appointment): JsonResponse
    {
        $this->authorizeAppointmentView($appointment);
        abort_unless(request()->user()->can('create-ophthalmology-registrations'), 403);

        $latestExam = $appointment->ophthalmologyRegistrations()
            ->latest('registration_date')
            ->first();

        return response()->json([
            'success' => true,
            'data' => [
                'doctors' => $this->doctors($appointment)->map(fn (Doctor $doctor) => [
                    'id' => $doctor->id,
                    'name' => $doctor->name,
                ])->values()->all(),
                'latest_prescription' => EyeGlassesOrder::prescriptionFromRefraction($latestExam?->refraction),
                'ophthalmology_registration_id' => $latestExam?->id,
                'frame_types' => EyeGlassesOrder::FRAME_TYPES,
                'lens_types' => EyeGlassesOrder::LENS_TYPES,
                'lens_materials' => EyeGlassesOrder::LENS_MATERIALS,
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

        $items = $appointment->eyeGlassesOrders()
            ->with('examiner:id,name')
            ->latest()
            ->get()
            ->map(fn (EyeGlassesOrder $item) => [
                'id' => $item->id,
                'ref_no' => $item->ref_no,
                'patient_name' => $patientName ?: null,
                'examiner_name' => $item->examiner?->name,
                'request_date' => $item->request_date
                    ? verta($item->request_date)->format('Y-m-d')
                    : null,
                'status' => $item->status,
                'frame_type' => $item->frame_type,
                'lens_type' => $item->lens_type,
                'urls' => [
                    'show' => route('react.eye-glasses-orders.show', $item),
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
        $examIds = $appointment->ophthalmologyRegistrations()->pluck('id')->all();

        $validated = $request->validate([
            'examiner_id' => ['nullable', Rule::in($doctorIds)],
            'ophthalmology_registration_id' => ['nullable', Rule::in($examIds)],
            'request_date' => ['required', 'string'],
            'frame_type' => ['nullable', Rule::in(EyeGlassesOrder::FRAME_TYPES)],
            'lens_type' => ['nullable', Rule::in(EyeGlassesOrder::LENS_TYPES)],
            'lens_material' => ['nullable', Rule::in(EyeGlassesOrder::LENS_MATERIALS)],
            'notes' => ['nullable', 'string'],
        ]);

        $exam = null;
        if (! empty($validated['ophthalmology_registration_id'])) {
            $exam = OphthalmologyRegistration::query()->find($validated['ophthalmology_registration_id']);
        } else {
            $exam = $appointment->ophthalmologyRegistrations()->latest('registration_date')->first();
        }

        $order = EyeGlassesOrder::create([
            'appointment_id' => $appointment->id,
            'ophthalmology_registration_id' => $exam?->id,
            'examiner_id' => $validated['examiner_id'] ?? $appointment->doctor_id,
            'branch_id' => $appointment->branch_id,
            'request_date' => Verta::parse($validated['request_date'])->datetime(),
            'frame_type' => $validated['frame_type'] ?? null,
            'lens_type' => $validated['lens_type'] ?? null,
            'lens_material' => $validated['lens_material'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'prescription' => EyeGlassesOrder::prescriptionFromRefraction($exam?->refraction),
        ]);

        return response()->json([
            'success' => true,
            'message' => localize('global.eye_glasses_order_created_successfully'),
            'data' => [
                'id' => $order->id,
                'url' => route('react.eye-glasses-orders.show', $order),
            ],
        ], 201);
    }

    private function doctors(Appointment $appointment)
    {
        return Doctor::query()
            ->where('active_status', true)
            ->where('is_eye_doctor', true)
            ->where('branch_id', $appointment->branch_id)
            ->orderBy('name')
            ->get();
    }
}
