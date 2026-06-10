<?php

namespace App\Http\Controllers\V1\ICUSections;

use App\Http\Controllers\Controller;
use App\Models\ICU;
use App\Models\ICUProcedure;
use App\Models\ICUProcedureType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProcedureController extends Controller
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

        $items = $icu->procedures()
            ->with([
                'procedure_type:id,name',
                'createdBy:id,name,last_name',
            ])
            ->latest('id')
            ->get()
            ->map(fn (ICUProcedure $procedure) => $this->formatProcedure($procedure))
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
                'procedure_types' => ICUProcedureType::query()
                    ->orderBy('name')
                    ->get(['id', 'name']),
            ],
        ]);
    }

    public function show(ICU $icu, ICUProcedure $icuProcedure): JsonResponse
    {
        $this->ensureAccessible($icu);
        abort_unless($this->canView(request()->user()), 403);
        $this->ensureProcedureBelongsToIcu($icu, $icuProcedure);

        $icuProcedure->loadMissing([
            'procedure_type:id,name',
            'createdBy:id,name,last_name',
        ]);

        return response()->json([
            'success' => true,
            'data' => $this->formatProcedure($icuProcedure),
        ]);
    }

    public function store(Request $request, ICU $icu): JsonResponse
    {
        $this->ensureAccessible($icu);
        abort_unless($icu->status === 'approved', 403);
        abort_if((bool) $icu->is_discharged, 403);

        $validated = $request->validate($this->validationRules());

        ICUProcedure::create([
            'icu_procedure_type_id' => $validated['icu_procedure_type_id'],
            'description' => $validated['description'],
            'i_c_u_id' => $icu->id,
        ]);

        return response()->json(['success' => true]);
    }

    public function update(Request $request, ICU $icu, ICUProcedure $icuProcedure): JsonResponse
    {
        $this->ensureAccessible($icu);
        abort_unless($request->user()->can('edit-icu-procedure'), 403);
        $this->ensureProcedureBelongsToIcu($icu, $icuProcedure);

        $validated = $request->validate($this->validationRules());

        $icuProcedure->update([
            'icu_procedure_type_id' => $validated['icu_procedure_type_id'],
            'description' => $validated['description'],
        ]);

        return response()->json(['success' => true]);
    }

    public function destroy(ICU $icu, ICUProcedure $icuProcedure): JsonResponse
    {
        $this->ensureAccessible($icu);
        abort_unless(request()->user()->can('delete-icu-procedure'), 403);
        $this->ensureProcedureBelongsToIcu($icu, $icuProcedure);

        $icuProcedure->delete();

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
            'edit' => $user->can('edit-icu-procedure'),
            'delete' => $user->can('delete-icu-procedure'),
        ];
    }

    /**
     * @return array<string, string>
     */
    private function validationRules(): array
    {
        return [
            'icu_procedure_type_id' => 'required|exists:i_c_u_procedure_types,id',
            'description' => 'required|string',
        ];
    }

    private function ensureProcedureBelongsToIcu(ICU $icu, ICUProcedure $procedure): void
    {
        abort_unless((int) $procedure->i_c_u_id === (int) $icu->id, 404);
    }

    /**
     * @return array<string, mixed>
     */
    private function formatProcedure(ICUProcedure $procedure): array
    {
        $createdByName = $procedure->createdBy
            ? trim(($procedure->createdBy->name ?? '').' '.($procedure->createdBy->last_name ?? ''))
            : null;

        return [
            'id' => $procedure->id,
            'icu_procedure_type_id' => $procedure->icu_procedure_type_id,
            'procedure_type_name' => $procedure->procedure_type?->name,
            'description' => $procedure->description,
            'created_by_name' => $createdByName ?: null,
            'created_at' => $procedure->created_at
                ? verta($procedure->created_at)->format('Y/m/d H:i')
                : null,
        ];
    }
}
