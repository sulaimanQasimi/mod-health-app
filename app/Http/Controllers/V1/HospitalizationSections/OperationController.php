<?php

namespace App\Http\Controllers\V1\HospitalizationSections;

use App\Http\Controllers\Controller;
use App\Http\Controllers\V1\Concerns\FormatsOperationSectionItems;
use App\Http\Controllers\V1\Concerns\ProvidesOperationReferralMeta;
use App\Models\Anesthesia;
use App\Models\Hospitalization;
use App\Services\AnesthesiaReferralService;
use App\Services\OperationReferralService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class OperationController extends Controller
{
    use FormatsOperationSectionItems;
    use ProvidesOperationReferralMeta;

    public function __construct(
        private readonly OperationReferralService $operationReferralService,
        private readonly AnesthesiaReferralService $anesthesiaReferralService,
    ) {}

    public function index(Hospitalization $hospitalization): JsonResponse
    {
        $this->ensureAccessible($hospitalization);

        $user = request()->user();
        if (! $this->canView($user)) {
            return response()->json([
                'success' => true,
                'data' => [
                    'items' => [],
                    'count' => 0,
                    'permissions' => [
                        'view' => false,
                        'create' => false,
                    ],
                ],
            ]);
        }

        $query = Anesthesia::query()
            ->where('is_referred_to_operation', true)
            ->with(['operationType:id,name', 'patient:id,name']);

        if ($hospitalization->appointment_id) {
            $query->where('appointment_id', $hospitalization->appointment_id);
        } else {
            $query->where('hospitalization_id', $hospitalization->id);
        }

        $items = $this->formatOperationSectionItems(
            $query->latest()->get()
        );

        return response()->json([
            'success' => true,
            'data' => [
                'items' => $items,
                'count' => count($items),
                'permissions' => [
                    'view' => true,
                    'create' => $this->canCreate($user, $hospitalization),
                ],
            ],
        ]);
    }

    public function meta(Hospitalization $hospitalization): JsonResponse
    {
        $this->ensureAccessible($hospitalization);
        abort_unless($this->canCreate(request()->user(), $hospitalization), 403);

        $hospitalization->loadMissing(['patient:id,name', 'room:id,name', 'bed:id,number', 'appointment:id,doctor_id']);
        $branchId = $hospitalization->branch_id ?? request()->user()->branch_id;

        return response()->json([
            'success' => true,
            'data' => [
                'patient_name' => $hospitalization->patient?->name,
                'current_room_name' => $hospitalization->room?->name,
                'current_bed_number' => $hospitalization->bed?->number,
                'will_clear_bed' => (bool) ($hospitalization->room_id || $hospitalization->bed_id),
                'operation_types' => $this->operationTypes($branchId),
                'hospital_doctors' => $this->hospitalDoctors($branchId),
            ],
        ]);
    }

    public function store(Request $request, Hospitalization $hospitalization): JsonResponse
    {
        $this->ensureAccessible($hospitalization);
        abort_if((bool) $hospitalization->is_discharged, 403);
        abort_unless($this->canCreate($request->user(), $hospitalization), 403);

        $hospitalization->loadMissing(['patient:id,name', 'appointment:id,doctor_id']);

        if (! $hospitalization->appointment_id) {
            return response()->json([
                'success' => false,
                'message' => localize('global.not_available'),
            ], 422);
        }

        $this->anesthesiaReferralService->normalizeReferralInput($request);

        $validated = $request->validate($this->anesthesiaReferralService->formValidationRules());
        $validated['hospitalization_id'] = $hospitalization->id;
        $validated['patient_id'] = $hospitalization->patient_id;
        $validated['appointment_id'] = $hospitalization->appointment_id;
        $validated['branch_id'] = $hospitalization->branch_id ?? $request->user()->branch_id;
        $validated['doctor_id'] = AnesthesiaReferralService::resolveDoctorId(
            $hospitalization->appointment?->doctor_id,
            null,
            $request->user(),
        );

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

    private function ensureAccessible(Hospitalization $hospitalization): void
    {
        abort_unless($hospitalization->userCanView(request()->user()), 404);
    }

    private function canView($user): bool
    {
        return $user?->can('show-operations-menu') ?? false;
    }

    private function canCreate($user, Hospitalization $hospitalization): bool
    {
        return ! (bool) $hospitalization->is_discharged
            && (bool) $hospitalization->appointment_id
            && ($user?->can('show-operations-menu') ?? false);
    }
}
