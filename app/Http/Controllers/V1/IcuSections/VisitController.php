<?php

namespace App\Http\Controllers\V1\ICUSections;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use App\Models\FoodType;
use App\Models\ICU;
use App\Models\Visit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class VisitController extends Controller
{
    public function index(ICU $icu): JsonResponse
    {
        $this->ensureAccessible($icu);

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
                        'edit' => false,
                        'delete' => false,
                    ],
                ],
            ]);
        }

        $foodTypes = FoodType::query()->orderBy('name')->get(['id', 'name'])->keyBy('id');

        $items = $icu->visits()
            ->with('doctor:id,name,last_name')
            ->latest('id')
            ->get()
            ->map(fn (Visit $visit) => $this->formatVisit($visit, $foodTypes))
            ->values()
            ->all();

        return response()->json([
            'success' => true,
            'data' => [
                'items' => $items,
                'count' => count($items),
                'permissions' => $this->permissions($user, $icu),
            ],
        ]);
    }

    public function meta(ICU $icu): JsonResponse
    {
        $this->ensureAccessible($icu);
        abort_unless($this->canView(request()->user()), 403);

        return response()->json([
            'success' => true,
            'data' => [
                'food_types' => FoodType::query()->orderBy('name')->get(['id', 'name']),
            ],
        ]);
    }

    public function show(ICU $icu, Visit $visit): JsonResponse
    {
        $this->ensureAccessible($icu);
        abort_unless($this->canView(request()->user()), 403);
        abort_unless((int) $visit->i_c_u_id === (int) $icu->id, 404);

        $foodTypes = FoodType::query()->orderBy('name')->get(['id', 'name'])->keyBy('id');

        return response()->json([
            'success' => true,
            'data' => $this->formatVisit($visit->loadMissing('doctor:id,name,last_name'), $foodTypes),
        ]);
    }

    public function store(Request $request, ICU $icu): JsonResponse
    {
        $this->ensureAccessible($icu);
        abort_unless($icu->status === 'approved', 403);
        abort_if((bool) $icu->is_discharged, 403);

        $validated = $request->validate($this->validationRules());

        Visit::create([
            ...$this->visitPayload($validated),
            'patient_id' => $icu->patient_id,
            'doctor_id' => $this->resolveDoctorUserId($icu),
            'i_c_u_id' => $icu->id,
        ]);

        return response()->json(['success' => true]);
    }

    public function update(Request $request, ICU $icu, Visit $visit): JsonResponse
    {
        $this->ensureAccessible($icu);
        abort_unless($request->user()->can('edit-icus'), 403);
        abort_unless((int) $visit->i_c_u_id === (int) $icu->id, 404);

        $validated = $request->validate($this->validationRules());

        $visit->update($this->visitPayload($validated));

        return response()->json(['success' => true]);
    }

    public function destroy(ICU $icu, Visit $visit): JsonResponse
    {
        $this->ensureAccessible($icu);
        abort_unless(request()->user()->can('delete-icus'), 403);
        abort_unless((int) $visit->i_c_u_id === (int) $icu->id, 404);

        $visit->delete();

        return response()->json(['success' => true]);
    }

    private function ensureAccessible(ICU $icu): void
    {
        abort_unless(request()->user()?->can('show-icu-menu'), 403);
    }

    private function canView($user): bool
    {
        return $user?->can('show-icu-menu') ?? false;
    }

    /**
     * @return array{view: bool, create: bool, edit: bool, delete: bool}
     */
    private function permissions($user, ICU $icu): array
    {
        $isActive = $icu->status === 'approved' && ! (bool) $icu->is_discharged;

        return [
            'view' => $this->canView($user),
            'create' => $isActive,
            'edit' => $user->can('edit-icus'),
            'delete' => $user->can('delete-icus'),
        ];
    }

    /**
     * @return array<string, string|array<int, string>>
     */
    private function validationRules(): array
    {
        return [
            'description' => 'required|string',
            'bp' => 'nullable|string|max:255',
            'pr' => 'nullable|string|max:255',
            'rr' => 'nullable|string|max:255',
            't' => 'nullable|string|max:255',
            'spo2' => 'nullable|string|max:255',
            'pain' => 'nullable|string|max:255',
            'antibiotic' => 'nullable|string|max:255',
            'food_type_id' => 'nullable|array',
            'food_type_id.*' => 'integer|exists:food_types,id',
            'intake' => 'nullable|string|max:255',
            'output' => 'nullable|string|max:255',
        ];
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    private function visitPayload(array $validated): array
    {
        $foodTypeIds = $validated['food_type_id'] ?? null;

        return [
            'description' => $validated['description'],
            'bp' => $validated['bp'] ?? null,
            'pr' => $validated['pr'] ?? null,
            'rr' => $validated['rr'] ?? null,
            't' => $validated['t'] ?? null,
            'spo2' => $validated['spo2'] ?? null,
            'pain' => $validated['pain'] ?? null,
            'antibiotic' => $validated['antibiotic'] ?? null,
            'food_type_id' => is_array($foodTypeIds) && count($foodTypeIds) > 0
                ? json_encode(array_values($foodTypeIds))
                : null,
            'intake' => $validated['intake'] ?? null,
            'output' => $validated['output'] ?? null,
        ];
    }

    private function resolveDoctorUserId(ICU $icu): int
    {
        if ($icu->doctor_id) {
            $doctor = Doctor::query()->find($icu->doctor_id);
            if ($doctor?->user_id) {
                return (int) $doctor->user_id;
            }
        }

        $icu->loadMissing('appointment.doctor');
        if ($icu->appointment?->doctor?->user_id) {
            return (int) $icu->appointment->doctor->user_id;
        }

        return (int) request()->user()->id;
    }

    /**
     * @param  Collection<int, FoodType>  $foodTypes
     * @return array<string, mixed>
     */
    private function formatVisit(Visit $visit, Collection $foodTypes): array
    {
        $foodTypeIds = $this->decodeFoodTypeIds($visit->food_type_id);
        $foodTypeNames = collect($foodTypeIds)
            ->map(fn (int $id) => $foodTypes->get($id)?->name)
            ->filter()
            ->values()
            ->all();

        $doctorName = $visit->doctor
            ? trim(($visit->doctor->name ?? '').' '.($visit->doctor->last_name ?? ''))
            : null;

        return [
            'id' => $visit->id,
            'description' => $visit->description,
            'doctor_name' => $doctorName ?: null,
            'visit_date' => $visit->created_at ? verta($visit->created_at)->format('Y/m/d') : null,
            'visit_time' => $visit->created_at?->format('H:i'),
            'bp' => $visit->bp,
            'pr' => $visit->pr,
            'rr' => $visit->rr,
            't' => $visit->t,
            'spo2' => $visit->spo2,
            'pain' => $visit->pain,
            'antibiotic' => $visit->antibiotic,
            'food_type_ids' => $foodTypeIds,
            'food_type_names' => $foodTypeNames,
            'intake' => $visit->intake,
            'output' => $visit->output,
        ];
    }

    /**
     * @return list<int>
     */
    private function decodeFoodTypeIds(mixed $foodTypeValue): array
    {
        if ($foodTypeValue === null || $foodTypeValue === '') {
            return [];
        }

        if (is_array($foodTypeValue)) {
            return array_values(array_map('intval', $foodTypeValue));
        }

        if (is_numeric($foodTypeValue)) {
            return [(int) $foodTypeValue];
        }

        if (! is_string($foodTypeValue)) {
            return [];
        }

        $decoded = json_decode($foodTypeValue, true);
        if (is_array($decoded)) {
            return array_values(array_map('intval', $decoded));
        }

        if (ctype_digit($foodTypeValue)) {
            return [(int) $foodTypeValue];
        }

        return collect(explode(',', $foodTypeValue))
            ->map(fn (string $id) => (int) trim($id))
            ->filter(fn (int $id) => $id > 0)
            ->values()
            ->all();
    }
}
