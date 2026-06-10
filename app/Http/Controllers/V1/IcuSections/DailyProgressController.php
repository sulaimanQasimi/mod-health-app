<?php

namespace App\Http\Controllers\V1\ICUSections;

use App\Http\Controllers\Controller;
use App\Models\DailyIcuProgress;
use App\Models\ICU;
use App\Models\LabType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DailyProgressController extends Controller
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

        $items = $icu->dailyProgress()
            ->with(['createdBy:id,name,last_name'])
            ->latest('id')
            ->get()
            ->map(fn (DailyIcuProgress $progress) => $this->formatListItem($progress))
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
                'lab_types' => LabType::query()
                    ->orderBy('name')
                    ->get(['id', 'name']),
            ],
        ]);
    }

    public function show(ICU $icu, DailyIcuProgress $dailyIcuProgress): JsonResponse
    {
        $this->ensureAccessible($icu);
        abort_unless($this->canView(request()->user()), 403);
        $this->ensureProgressBelongsToIcu($icu, $dailyIcuProgress);

        $dailyIcuProgress->loadMissing(['createdBy:id,name,last_name']);

        return response()->json([
            'success' => true,
            'data' => $this->formatDetail($dailyIcuProgress),
        ]);
    }

    public function store(Request $request, ICU $icu): JsonResponse
    {
        $this->ensureAccessible($icu);
        abort_unless($icu->status === 'approved', 403);
        abort_if((bool) $icu->is_discharged, 403);
        abort_unless($this->canView($request->user()), 403);

        $validated = $request->validate($this->validationRules());

        DailyIcuProgress::create([
            ...$validated,
            'i_c_u_id' => $icu->id,
            'lab_ids' => $this->encodeLabIds($validated['lab_ids'] ?? null),
        ]);

        return response()->json(['success' => true]);
    }

    public function update(Request $request, ICU $icu, DailyIcuProgress $dailyIcuProgress): JsonResponse
    {
        $this->ensureAccessible($icu);
        abort_unless($request->user()->can('edit-daily-icu-progress'), 403);
        $this->ensureProgressBelongsToIcu($icu, $dailyIcuProgress);

        $validated = $request->validate($this->validationRules());

        $dailyIcuProgress->update([
            ...$validated,
            'lab_ids' => $this->encodeLabIds($validated['lab_ids'] ?? null),
        ]);

        return response()->json(['success' => true]);
    }

    public function destroy(ICU $icu, DailyIcuProgress $dailyIcuProgress): JsonResponse
    {
        $this->ensureAccessible($icu);
        abort_unless(request()->user()->can('edit-daily-icu-progress'), 403);
        $this->ensureProgressBelongsToIcu($icu, $dailyIcuProgress);

        $dailyIcuProgress->delete();

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
            'create' => $isActive && $this->canView($user),
            'edit' => $user->can('edit-daily-icu-progress'),
            'delete' => $user->can('edit-daily-icu-progress'),
        ];
    }

    /**
     * @return array<string, string>
     */
    private function validationRules(): array
    {
        return [
            'icu_day' => 'nullable|string|max:255',
            'icu_diagnose' => 'nullable|string|max:255',
            'daily_events' => 'nullable|string|max:255',
            'hr' => 'nullable|string|max:255',
            'bp' => 'nullable|string|max:255',
            'spo2' => 'nullable|string|max:255',
            't' => 'nullable|string|max:255',
            'rr' => 'nullable|string|max:255',
            'gcs' => 'nullable|string|max:255',
            'cvs' => 'nullable|string|max:255',
            'pupils' => 'nullable|string|max:255',
            's1s2' => 'nullable|string|max:255',
            'rs' => 'nullable|string|max:255',
            'gi' => 'nullable|string|max:255',
            'renal' => 'nullable|string|max:255',
            'musculoskeletal_system' => 'nullable|string|max:255',
            'extremities' => 'nullable|string|max:255',
            'assesment' => 'nullable|string|max:1000',
            'plan' => 'nullable|string|max:2000',
            'lab_ids' => 'nullable|array',
            'lab_ids.*' => 'integer|exists:lab_types,id',
        ];
    }

    private function ensureProgressBelongsToIcu(ICU $icu, DailyIcuProgress $progress): void
    {
        abort_unless((int) $progress->i_c_u_id === (int) $icu->id, 404);
    }

    /**
     * @param  array<int>|null  $labIds
     */
    private function encodeLabIds(?array $labIds): ?string
    {
        if ($labIds === null || $labIds === []) {
            return null;
        }

        return json_encode(array_values($labIds));
    }

    /**
     * @return list<int>
     */
    private function decodeLabIds(?string $labIds): array
    {
        if (! $labIds) {
            return [];
        }

        $decoded = json_decode($labIds, true);

        if (! is_array($decoded)) {
            return [];
        }

        return array_values(array_map('intval', $decoded));
    }

    /**
     * @return array<string, mixed>
     */
    private function formatListItem(DailyIcuProgress $progress): array
    {
        return [
            'id' => $progress->id,
            'icu_day' => $progress->icu_day,
            'created_by_name' => $this->formatUserName($progress->createdBy),
            'created_at' => $progress->created_at
                ? verta($progress->created_at)->format('Y/m/d H:i')
                : null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function formatDetail(DailyIcuProgress $progress): array
    {
        $labIds = $this->decodeLabIds($progress->lab_ids);
        $labTypes = $labIds === []
            ? collect()
            : LabType::query()->whereIn('id', $labIds)->orderBy('name')->get(['id', 'name']);

        return [
            'id' => $progress->id,
            'icu_day' => $progress->icu_day,
            'icu_diagnose' => $progress->icu_diagnose,
            'daily_events' => $progress->daily_events,
            'hr' => $progress->hr,
            'bp' => $progress->bp,
            'spo2' => $progress->spo2,
            't' => $progress->t,
            'rr' => $progress->rr,
            'gcs' => $progress->gcs,
            'cvs' => $progress->cvs,
            'pupils' => $progress->pupils,
            's1s2' => $progress->s1s2,
            'rs' => $progress->rs,
            'gi' => $progress->gi,
            'renal' => $progress->renal,
            'musculoskeletal_system' => $progress->musculoskeletal_system,
            'extremities' => $progress->extremities,
            'assesment' => $progress->assesment,
            'plan' => $progress->plan,
            'lab_ids' => $labIds,
            'lab_type_names' => $labTypes->pluck('name')->values()->all(),
            'created_by_name' => $this->formatUserName($progress->createdBy),
            'created_at' => $progress->created_at
                ? verta($progress->created_at)->format('Y/m/d H:i')
                : null,
        ];
    }

    private function formatUserName($user): ?string
    {
        if (! $user) {
            return null;
        }

        $name = trim(($user->name ?? '').' '.($user->last_name ?? ''));

        return $name !== '' ? $name : null;
    }
}
