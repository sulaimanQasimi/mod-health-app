<?php

namespace App\Http\Controllers\V1\AppointmentSections;

use App\Http\Controllers\Controller;
use App\Http\Controllers\V1\AppointmentSections\Concerns\AuthorizesAppointmentAccess;
use App\Http\Controllers\V1\Concerns\FormatsOperationSectionItems;
use App\Http\Controllers\V1\Concerns\ProvidesOperationReferralMeta;
use App\Models\Appointment;
use App\Models\Hospitalization;
use App\Services\AnesthesiaReferralService;
use App\Services\OperationReferralService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class OperationController extends Controller
{
    use AuthorizesAppointmentAccess;
    use FormatsOperationSectionItems;
    use ProvidesOperationReferralMeta;

    public function __construct(
        private readonly OperationReferralService $operationReferralService,
        private readonly AnesthesiaReferralService $anesthesiaReferralService,
    ) {}

    public function index(Appointment $appointment): JsonResponse
    {
        $this->authorizeAppointmentView($appointment);
        $user = request()->user();

        if (! $user?->can('show-operations-menu')) {
            return $this->sectionIndexResponse([], $appointment, ['view' => false, 'create' => false]);
        }

        $items = $this->formatOperationSectionItems(
            $appointment->anesthesias()
                ->where('is_referred_to_operation', true)
                ->with(['operationType:id,name', 'patient:id,name'])
                ->latest()
                ->get()
        );

        return $this->sectionIndexResponse($items, $appointment, [
            'view' => true,
            'create' => $this->canCreate($user, $appointment),
        ]);
    }

    public function meta(Appointment $appointment): JsonResponse
    {
        $this->authorizeAppointmentView($appointment);
        abort_unless($this->canCreate(request()->user(), $appointment), 403);

        $appointment->loadMissing('patient:id,name');
        $hospitalization = $this->activeHospitalization($appointment);
        $branchId = $appointment->branch_id ?? request()->user()->branch_id;

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
        abort_unless($this->canCreate($request->user(), $appointment), 403);

        $this->anesthesiaReferralService->normalizeReferralInput($request);

        $validated = $request->validate($this->anesthesiaReferralService->formValidationRules());
        $validated['patient_id'] = $appointment->patient_id;
        $validated['appointment_id'] = $appointment->id;
        $validated['branch_id'] = $appointment->branch_id ?? $request->user()->branch_id;
        $validated['doctor_id'] = AnesthesiaReferralService::resolveDoctorId(
            $appointment->doctor_id,
            null,
            $request->user(),
        );

        $hospitalization = $this->activeHospitalization($appointment);
        if ($hospitalization) {
            $validated['hospitalization_id'] = $hospitalization->id;
        }

        try {
            $this->operationReferralService->createDirect($validated);
        } catch (ValidationException $exception) {
            return response()->json([
                'success' => false,
                'message' => collect($exception->errors())->flatten()->first(),
                'errors' => $exception->errors(),
            ], 422);
        }

        return response()->json(['success' => true]);
    }

    private function canCreate($user, Appointment $appointment): bool
    {
        return ! $appointment->is_completed
            && ($user?->can('show-operations-menu') ?? false);
    }

    private function activeHospitalization(Appointment $appointment): ?Hospitalization
    {
        return Hospitalization::query()
            ->where('appointment_id', $appointment->id)
            ->where(function ($query) {
                $query->where('is_discharged', 0)->orWhereNull('is_discharged');
            })
            ->with(['room:id,name', 'bed:id,number'])
            ->latest('id')
            ->first();
    }
}
