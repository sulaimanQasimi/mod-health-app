<?php

namespace App\Http\Controllers\V1\HospitalizationSections;

use App\Http\Controllers\Controller;
use App\Models\Hospitalization;
use App\Models\NutritionCare;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NutritionCareController extends Controller
{
    private const MORPHABLE_TYPE = Hospitalization::class;

    /** @var list<string> */
    private const OBSERVATION_FIELDS = [
        'cough',
        'sound',
        'fluid_swallowing_ability',
        'weight',
        'amount_and_type_of_nutrition',
        'diarrhea',
        'heart_failure_and_kidney_disease',
        'remaining_materials',
        'type_of_tube',
    ];

    /** @var list<string> */
    private const INTERVENTION_FIELDS = [
        'constipation',
        'nutrition_is_provided',
        'mouth_hygiene',
        'oral_nutrition_advices',
        'voice_exercise',
        'swallowing_exercise',
        'aspiration_prevention_proceeded',
    ];

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

        $items = NutritionCare::query()
            ->where('morphable_type', self::MORPHABLE_TYPE)
            ->where('morphable_id', $hospitalization->id)
            ->with(['nurse:id,first_name,last_name'])
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (NutritionCare $care) => $this->formatCare($care, $hospitalization))
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

        $user = request()->user();
        abort_unless($this->canView($user) && $user->can('create', NutritionCare::class), 403);

        $hospitalization->loadMissing('patient:id,name');
        $nurse = $user->nurse;

        return response()->json([
            'success' => true,
            'data' => [
                'patient_name' => $hospitalization->patient?->name,
                'current_nurse' => $nurse ? [
                    'id' => $nurse->id,
                    'name' => $nurse->full_name,
                ] : null,
            ],
        ]);
    }

    public function show(Hospitalization $hospitalization, NutritionCare $nutritionCare): JsonResponse
    {
        $this->ensureAccessible($hospitalization);
        $this->ensureCareBelongsToHospitalization($hospitalization, $nutritionCare);
        abort_unless($this->canView(request()->user()), 403);

        $nutritionCare->load(['nurse:id,first_name,last_name']);

        return response()->json([
            'success' => true,
            'data' => $this->formatCare($nutritionCare, $hospitalization),
        ]);
    }

    public function store(Request $request, Hospitalization $hospitalization): JsonResponse
    {
        $this->ensureAccessible($hospitalization);
        abort_if((bool) $hospitalization->is_discharged, 403);
        $this->authorize('create', NutritionCare::class);

        $nurse = $request->user()->nurse;
        abort_unless($nurse, 403);

        $hospitalization->loadMissing('patient:id,name');
        $validated = $request->validate($this->validationRules());

        NutritionCare::create([
            ...$this->carePayload($validated),
            'patient_name' => $hospitalization->patient?->name ?? '—',
            'nurse_id' => $nurse->id,
            'morphable_type' => self::MORPHABLE_TYPE,
            'morphable_id' => $hospitalization->id,
        ]);

        return response()->json(['success' => true]);
    }

    public function update(Request $request, Hospitalization $hospitalization, NutritionCare $nutritionCare): JsonResponse
    {
        $this->ensureAccessible($hospitalization);
        abort_if((bool) $hospitalization->is_discharged, 403);
        $this->ensureCareBelongsToHospitalization($hospitalization, $nutritionCare);
        $this->authorize('update', $nutritionCare);

        $validated = $request->validate($this->validationRules());

        $nutritionCare->update($this->carePayload($validated));

        return response()->json(['success' => true]);
    }

    public function destroy(Hospitalization $hospitalization, NutritionCare $nutritionCare): JsonResponse
    {
        $this->ensureAccessible($hospitalization);
        abort_if((bool) $hospitalization->is_discharged, 403);
        $this->ensureCareBelongsToHospitalization($hospitalization, $nutritionCare);
        $this->authorize('delete', $nutritionCare);

        $nutritionCare->delete();

        return response()->json(['success' => true]);
    }

    private function ensureAccessible(Hospitalization $hospitalization): void
    {
        abort_unless($hospitalization->userCanView(request()->user()), 404);
    }

    private function ensureCareBelongsToHospitalization(
        Hospitalization $hospitalization,
        NutritionCare $nutritionCare,
    ): void {
        abort_unless(
            $nutritionCare->morphable_type === self::MORPHABLE_TYPE
            && (int) $nutritionCare->morphable_id === (int) $hospitalization->id,
            404,
        );
    }

    private function canView($user): bool
    {
        return $user?->can('viewAny', NutritionCare::class) ?? false;
    }

    /**
     * @return array{view: bool, create: bool}
     */
    private function permissions($user, Hospitalization $hospitalization): array
    {
        return [
            'view' => $this->canView($user),
            'create' => ! (bool) $hospitalization->is_discharged && $user->can('create', NutritionCare::class),
        ];
    }

    /**
     * @return array<string, string|array<int, string>>
     */
    private function validationRules(): array
    {
        $rules = [
            'nutrition_care_full_note' => 'nullable|string|max:5000',
        ];

        foreach ([...self::OBSERVATION_FIELDS, ...self::INTERVENTION_FIELDS] as $field) {
            $rules[$field] = 'nullable|boolean';
        }

        return $rules;
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    private function carePayload(array $validated): array
    {
        $payload = [
            'nutrition_care_full_note' => $validated['nutrition_care_full_note'] ?? null,
        ];

        foreach ([...self::OBSERVATION_FIELDS, ...self::INTERVENTION_FIELDS] as $field) {
            $payload[$field] = (bool) ($validated[$field] ?? false);
        }

        return $payload;
    }

    /**
     * @return array<string, mixed>
     */
    private function formatCare(NutritionCare $care, Hospitalization $hospitalization): array
    {
        $user = request()->user();
        $locked = (bool) $hospitalization->is_discharged;
        $data = [
            'id' => $care->id,
            'patient_name' => $care->patient_name,
            'nurse_name' => $care->nurse?->full_name,
            'recorded_at' => $care->created_at ? verta($care->created_at)->format('Y/m/d H:i') : null,
            'nutrition_care_full_note' => $care->nutrition_care_full_note,
            'permissions' => [
                'edit' => ! $locked && $user->can('update', $care),
                'delete' => ! $locked && $user->can('delete', $care),
            ],
            'urls' => [
                'print' => route('nutrition-cares.print', $care),
            ],
        ];

        foreach ([...self::OBSERVATION_FIELDS, ...self::INTERVENTION_FIELDS] as $field) {
            $data[$field] = (bool) $care->{$field};
        }

        return $data;
    }
}
