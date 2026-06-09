<?php

namespace App\Http\Controllers\V1\HospitalizationSections;

use App\Http\Controllers\Controller;
use App\Models\Hospitalization;
use App\Models\Visit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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

        $items = $hospitalization->visits()
            ->with('doctor:id,name')
            ->latest('id')
            ->get()
            ->map(fn (Visit $visit) => [
                'id' => $visit->id,
                'description' => $visit->description,
                'doctor_name' => $visit->doctor?->name,
                'visit_date' => $visit->created_at ? verta($visit->created_at)->format('Y/m/d') : null,
            ])
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

    public function store(Request $request, Hospitalization $hospitalization): JsonResponse
    {
        $this->ensureAccessible($hospitalization);
        abort_if((bool) $hospitalization->is_discharged, 403);

        $validated = $request->validate([
            'description' => 'required|string',
        ]);

        Visit::create([
            'description' => $validated['description'],
            'patient_id' => $hospitalization->patient_id,
            'doctor_id' => $hospitalization->doctor_id,
            'hospitalization_id' => $hospitalization->id,
        ]);

        return response()->json(['success' => true]);
    }

    public function update(Request $request, Hospitalization $hospitalization, Visit $visit): JsonResponse
    {
        $this->ensureAccessible($hospitalization);
        abort_unless($request->user()->can('edit-hospitalizations'), 403);
        abort_unless((int) $visit->hospitalization_id === (int) $hospitalization->id, 404);

        $validated = $request->validate([
            'description' => 'required|string',
        ]);

        $visit->update($validated);

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
}
