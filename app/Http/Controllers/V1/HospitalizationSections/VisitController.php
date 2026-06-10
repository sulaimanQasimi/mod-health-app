<?php

namespace App\Http\Controllers\V1\HospitalizationSections;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use App\Models\FoodType;
use App\Models\Hospitalization;
use App\Models\Visit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class VisitController extends Controller
{
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
                        'edit' => false,
                        'delete' => false,
                    ],
                ],
            ]);
        }

        $foodTypes = FoodType::query()->orderBy('name')->get(['id', 'name'])->keyBy('id');

        $items = $hospitalization->visits()
            ->with('doctor:id,name')
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
                'permissions' => $this->permissions($user, $hospitalization),
            ],
        ]);
    }

    public function meta(Hospitalization $hospitalization): JsonResponse
    {
        $this->ensureAccessible($hospitalization);
        abort_unless($this->canView(request()->user()), 403);

        return response()->json([
            'success' => true,
            'data' => [
                'food_types' => FoodType::query()->orderBy('name')->get(['id', 'name']),
            ],
        ]);
    }

    public function store(Request $request, Hospitalization $hospitalization): JsonResponse
    {
        $this->ensureAccessible($hospitalization);
        abort_if((bool) $hospitalization->is_discharged, 403);

        $validated = $request->validate($this->validationRules());

        Visit::create([
            ...$this->visitPayload($validated),
            'patient_id' => $hospitalization->patient_id,
            'doctor_id' => $this->resolveDoctorUserId($hospitalization),
            'hospitalization_id' => $hospitalization->id,
        ]);

        return response()->json(['success' => true]);
    }

    public function update(Request $request, Hospitalization $hospitalization, Visit $visit): JsonResponse
    {
        $this->ensureAccessible($hospitalization);
        abort_unless($request->user()->can('edit-hospitalizations'), 403);
        abort_unless((int) $visit->hospitalization_id === (int) $hospitalization->id, 404);

        $validated = $request->validate($this->validationRules());

        $visit->update($this->visitPayload($validated));

        return response()->json(['success' => true]);
    }

    public function destroy(Hospitalization $hospitalization, Visit $visit): JsonResponse
    {
        $this->ensureAccessible($hospitalization);
        abort_unless(request()->user()->can('delete-hospitalizations'), 403);
        abort_unless((int) $visit->hospitalization_id === (int) $hospitalization->id, 404);

        $visit->delete();

        return response()->json(['success' => true]);
    }

    private function ensureAccessible(Hospitalization $hospitalization): void
    {
        abort_unless($hospitalization->userCanView(request()->user()), 404);
    }

    private function canView($user): bool
    {
        return $user?->can('show-hospitalizations-menu') ?? false;
    }

    /**
     * @return array{view: bool, create: bool, edit: bool, delete: bool}
     */
    private function permissions($user, Hospitalization $hospitalization): array
    {
        return [
            'view' => $this->canView($user),
            'create' => ! (bool) $hospitalization->is_discharged,
            'edit' => $user->can('edit-hospitalizations'),
            'delete' => $user->can('delete-hospitalizations'),
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

    private function resolveDoctorUserId(Hospitalization $hospitalization): int
    {
        if (! $hospitalization->doctor_id) {
            return (int) request()->user()->id;
        }

        $doctor = Doctor::query()->find($hospitalization->doctor_id);

        return (int) ($doctor?->user_id ?: request()->user()->id);
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

        return [
            'id' => $visit->id,
            'description' => $visit->description,
            'doctor_name' => $visit->doctor?->name,
            'visit_date' => $visit->created_at ? verta($visit->created_at)->format('Y/m/d') : null,
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
    private function decodeFoodTypeIds(?string $foodTypeJson): array
    {
        if (! $foodTypeJson) {
            return [];
        }

        $decoded = json_decode($foodTypeJson, true);

        if (! is_array($decoded)) {
            return [];
        }

        return array_values(array_map('intval', $decoded));
    }
}
