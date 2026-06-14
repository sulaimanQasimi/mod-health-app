<?php

namespace App\Http\Controllers\V1\HospitalizationSections;

use App\Http\Controllers\Controller;
use App\Models\Anesthesia;
use App\Models\Doctor;
use App\Models\Hospitalization;
use App\Models\OperationType;
use App\Services\AnesthesiaReferralService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AnesthesiaController extends Controller
{
    public function __construct(
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

        $items = $hospitalization->anesthesias()
            ->with(['operationType:id,name', 'patient:id,name'])
            ->latest()
            ->get()
            ->map(fn (Anesthesia $item) => $this->formatListItem($item))
            ->values()
            ->all();

        return response()->json([
            'success' => true,
            'data' => [
                'items' => $items,
                'count' => count($items),
                'permissions' => $this->permissions($user, $hospitalization),
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

    public function destroy(Hospitalization $hospitalization, Anesthesia $anesthesia): JsonResponse
    {
        $this->ensureAccessible($hospitalization);
        abort_if((bool) $hospitalization->is_discharged, 403);
        abort_unless(request()->user()->can('delete-anesthesias'), 403);
        abort_unless((int) $anesthesia->hospitalization_id === (int) $hospitalization->id, 404);

        $anesthesia->delete();

        return response()->json(['success' => true]);
    }

    /**
     * @return array<string, mixed>
     */
    private function formatListItem(Anesthesia $item): array
    {
        return [
            'id' => $item->id,
            'operation_type' => $item->operationType?->name,
            'patient_name' => $item->patient?->name,
            'status' => $item->status,
            'date' => $item->date ? verta($item->date)->format('Y-m-d') : null,
            'urls' => [
                'show' => route('react.anesthesias.show', $item),
                'edit' => route('react.anesthesias.edit', $item),
            ],
        ];
    }

    private function ensureAccessible(Hospitalization $hospitalization): void
    {
        abort_unless($hospitalization->userCanView(request()->user()), 404);
    }

    private function canView($user): bool
    {
        return ($user?->can('refer-to-anesthesia') ?? false)
            || ($user?->can('edit-anesthesias') ?? false)
            || ($user?->can('delete-anesthesias') ?? false)
            || ($user?->can('show-anesthesias-menu') ?? false);
    }

    private function canCreate($user, Hospitalization $hospitalization): bool
    {
        return ! (bool) $hospitalization->is_discharged
            && ($user?->can('refer-to-anesthesia') ?? false)
            && (bool) $hospitalization->appointment_id;
    }

    /**
     * @return array{view: bool, create: bool, edit: bool, delete: bool}
     */
    private function permissions($user, Hospitalization $hospitalization): array
    {
        return [
            'view' => $this->canView($user),
            'create' => $this->canCreate($user, $hospitalization),
            'edit' => ! (bool) $hospitalization->is_discharged && ($user?->can('edit-anesthesias') ?? false),
            'delete' => ! (bool) $hospitalization->is_discharged && ($user?->can('delete-anesthesias') ?? false),
        ];
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
