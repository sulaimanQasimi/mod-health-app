<?php

namespace App\Http\Controllers\V1\AppointmentSections;

use App\Http\Controllers\Controller;
use App\Http\Controllers\V1\AppointmentSections\Concerns\AuthorizesAppointmentAccess;
use App\Models\Anesthesia;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Hospitalization;
use App\Models\OperationType;
use App\Services\AnesthesiaReferralService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AnesthesiaController extends Controller
{
    use AuthorizesAppointmentAccess;

    public function __construct(
        private readonly AnesthesiaReferralService $anesthesiaReferralService,
    ) {}

    public function index(Appointment $appointment): JsonResponse
    {
        $this->authorizeAppointmentView($appointment);
        $user = request()->user();

        if (! $this->canView($user)) {
            return $this->sectionIndexResponse([], $appointment, ['view' => false, 'create' => false]);
        }

        $items = $appointment->anesthesias()
            ->with(['operationType:id,name', 'patient:id,name'])
            ->latest()
            ->get()
            ->map(fn (Anesthesia $item) => [
                'id' => $item->id,
                'operation_type' => $item->operationType?->name,
                'patient_name' => $item->patient?->name,
                'status' => $item->status,
                'date' => $item->date ? verta($item->date)->format('Y-m-d') : null,
                'urls' => [
                    'show' => route('react.anesthesias.show', $item),
                    'edit' => route('react.anesthesias.edit', $item),
                ],
            ])
            ->values()
            ->all();

        return $this->sectionIndexResponse($items, $appointment, [
            'view' => true,
            'create' => ! $appointment->is_completed && $user->can('refer-to-anesthesia'),
            'edit' => $user->can('edit-anesthesias'),
            'delete' => $user->can('delete-anesthesias'),
        ]);
    }

    public function meta(Appointment $appointment): JsonResponse
    {
        $this->authorizeAppointmentView($appointment);
        abort_unless(! $appointment->is_completed && request()->user()->can('refer-to-anesthesia'), 403);

        $appointment->loadMissing('patient:id,name');
        $branchId = $appointment->branch_id ?? request()->user()->branch_id;

        $hospitalization = Hospitalization::query()
            ->where('appointment_id', $appointment->id)
            ->where(function ($query) {
                $query->where('is_discharged', 0)->orWhereNull('is_discharged');
            })
            ->with(['room:id,name', 'bed:id,number'])
            ->latest('id')
            ->first();

        return response()->json([
            'success' => true,
            'data' => [
                'patient_name' => $appointment->patient?->name,
                'current_room_name' => $hospitalization?->room?->name,
                'current_bed_number' => $hospitalization?->bed?->number,
                'will_clear_bed' => (bool) ($hospitalization?->room_id || $hospitalization?->bed_id),
                'operation_types' => $this->operationTypes($branchId),
                'hospital_doctors' => $this->hospitalDoctors($branchId),
            ],
        ]);
    }

    public function store(Request $request, Appointment $appointment): JsonResponse
    {
        $this->authorizeAppointmentView($appointment);
        abort_unless(! $appointment->is_completed && $request->user()->can('refer-to-anesthesia'), 403);

        $validated = $request->validate($this->anesthesiaReferralService->validationRules());
        $validated['patient_id'] = $appointment->patient_id;
        $validated['appointment_id'] = $appointment->id;
        $validated['branch_id'] = $appointment->branch_id ?? $request->user()->branch_id;
        $validated['doctor_id'] = AnesthesiaReferralService::resolveDoctorId(
            $appointment->doctor_id,
            isset($validated['doctor_id']) ? (int) $validated['doctor_id'] : null,
            $request->user(),
        );

        $hospitalization = Hospitalization::query()
            ->where('appointment_id', $appointment->id)
            ->where(function ($query) {
                $query->where('is_discharged', 0)->orWhereNull('is_discharged');
            })
            ->latest('id')
            ->first();

        if ($hospitalization) {
            $validated['hospitalization_id'] = $hospitalization->id;
        }

        try {
            $this->anesthesiaReferralService->create($validated, $request->user());
        } catch (ValidationException $exception) {
            return response()->json([
                'success' => false,
                'message' => collect($exception->errors())->flatten()->first(),
                'errors' => $exception->errors(),
            ], 422);
        }

        return response()->json(['success' => true]);
    }

    public function destroy(Appointment $appointment, Anesthesia $anesthesia): JsonResponse
    {
        $this->authorizeAppointmentView($appointment);
        abort_unless(request()->user()->can('delete-anesthesias'), 403);
        abort_unless((int) $anesthesia->appointment_id === (int) $appointment->id, 404);
        $anesthesia->delete();

        return response()->json(['success' => true]);
    }

    private function canView($user): bool
    {
        return ($user?->can('refer-to-anesthesia') ?? false)
            || ($user?->can('edit-anesthesias') ?? false)
            || ($user?->can('delete-anesthesias') ?? false)
            || ($user?->can('show-anesthesias-menu') ?? false);
    }

    /**
     * @return list<array{id: int, name: string}>
     */
    private function operationTypes(?int $branchId): array
    {
        return OperationType::query()
            ->when($branchId, fn ($query) => $query->where('branch_id', $branchId))
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (OperationType $type) => ['id' => $type->id, 'name' => $type->name])
            ->values()
            ->all();
    }

    /**
     * @return list<array{id: int, name: string}>
     */
    private function hospitalDoctors(?int $branchId): array
    {
        return Doctor::query()
            ->where('clinic_type', 'hospital')
            ->where('active_status', true)
            ->when($branchId, fn ($query) => $query->where('branch_id', $branchId))
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (Doctor $doctor) => ['id' => $doctor->id, 'name' => $doctor->name])
            ->values()
            ->all();
    }
}
