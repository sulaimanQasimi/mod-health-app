<?php

namespace App\Http\Controllers\V1\HospitalizationSections;

use App\Http\Controllers\Controller;
use App\Models\Hospitalization;
use App\Models\NurseNote;
use Hekmatinasser\Verta\Verta;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NurseNoteController extends Controller
{
    private const MORPHABLE_TYPE = Hospitalization::class;

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
                    'urls' => ['print' => null],
                ],
            ]);
        }

        $items = NurseNote::query()
            ->where('morphable_type', self::MORPHABLE_TYPE)
            ->where('morphable_id', $hospitalization->id)
            ->with(['nurse:id,first_name,last_name', 'createdBy:id,name'])
            ->orderByDesc('date')
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (NurseNote $note) => $this->formatNote($note))
            ->values()
            ->all();

        return response()->json([
            'success' => true,
            'data' => [
                'items' => $items,
                'count' => count($items),
                'permissions' => $this->permissions($user, $hospitalization),
                'urls' => [
                    'print' => count($items) > 0
                        ? route('nurse-notes.print', [
                            'morphable_type' => self::MORPHABLE_TYPE,
                            'morphable_id' => $hospitalization->id,
                        ])
                        : null,
                ],
            ],
        ]);
    }

    public function meta(Hospitalization $hospitalization): JsonResponse
    {
        $this->ensureAccessible($hospitalization);

        $user = request()->user();
        abort_unless(
            $this->canView($user) && (
                $user->can('create', NurseNote::class)
                || $user->hasPermissionTo('edit-nurse-notes')
                || $user->hasRole(['super_admin', 'admin', 'hr', 'nurse'])
            ),
            403,
        );

        $nurse = $user->nurse;

        return response()->json([
            'success' => true,
            'data' => [
                'default_date' => verta()->format('Y/m/d'),
                'current_nurse' => $nurse ? [
                    'id' => $nurse->id,
                    'name' => $nurse->full_name,
                ] : null,
            ],
        ]);
    }

    public function show(Hospitalization $hospitalization, NurseNote $nurseNote): JsonResponse
    {
        $this->ensureAccessible($hospitalization);
        $this->ensureNoteBelongsToHospitalization($hospitalization, $nurseNote);
        abort_unless($this->canView(request()->user()), 403);

        $nurseNote->load(['nurse:id,first_name,last_name', 'createdBy:id,name']);

        return response()->json([
            'success' => true,
            'data' => $this->formatNote($nurseNote),
        ]);
    }

    public function store(Request $request, Hospitalization $hospitalization): JsonResponse
    {
        $this->ensureAccessible($hospitalization);
        abort_if((bool) $hospitalization->is_discharged, 403);
        $this->authorize('create', NurseNote::class);

        $nurse = $request->user()->nurse;
        abort_unless($nurse, 403);

        $validated = $request->validate($this->validationRules());
        $validated['date'] = Verta::parse($validated['date'])->toCarbon();

        NurseNote::create([
            ...$this->notePayload($validated),
            'nurse_id' => $nurse->id,
            'morphable_type' => self::MORPHABLE_TYPE,
            'morphable_id' => $hospitalization->id,
        ]);

        return response()->json(['success' => true]);
    }

    public function update(Request $request, Hospitalization $hospitalization, NurseNote $nurseNote): JsonResponse
    {
        $this->ensureAccessible($hospitalization);
        $this->ensureNoteBelongsToHospitalization($hospitalization, $nurseNote);
        $this->authorize('update', $nurseNote);

        $validated = $request->validate($this->validationRules());
        $validated['date'] = Verta::parse($validated['date'])->toCarbon();

        $nurseNote->update($this->notePayload($validated));

        return response()->json(['success' => true]);
    }

    public function destroy(Hospitalization $hospitalization, NurseNote $nurseNote): JsonResponse
    {
        $this->ensureAccessible($hospitalization);
        $this->ensureNoteBelongsToHospitalization($hospitalization, $nurseNote);
        $this->authorize('delete', $nurseNote);

        $nurseNote->delete();

        return response()->json(['success' => true]);
    }

    private function ensureAccessible(Hospitalization $hospitalization): void
    {
        abort_unless($hospitalization->userCanView(request()->user()), 404);
    }

    private function ensureNoteBelongsToHospitalization(
        Hospitalization $hospitalization,
        NurseNote $nurseNote,
    ): void {
        abort_unless(
            $nurseNote->morphable_type === self::MORPHABLE_TYPE
            && (int) $nurseNote->morphable_id === (int) $hospitalization->id,
            404,
        );
    }

    private function canView($user): bool
    {
        return $user?->can('viewAny', NurseNote::class) ?? false;
    }

    /**
     * @return array{view: bool, create: bool, edit: bool, delete: bool}
     */
    private function permissions($user, Hospitalization $hospitalization): array
    {
        return [
            'view' => $this->canView($user),
            'create' => ! (bool) $hospitalization->is_discharged && $user->can('create', NurseNote::class),
            'edit' => $user->can('create', NurseNote::class)
                || $user->hasPermissionTo('edit-nurse-notes')
                || $user->hasRole(['super_admin', 'admin', 'hr', 'nurse']),
            'delete' => $user->hasPermissionTo('delete-nurse-notes')
                || $user->hasRole(['super_admin', 'admin', 'hr', 'nurse']),
        ];
    }

    /**
     * @return array<string, string|array<int, string>>
     */
    private function validationRules(): array
    {
        return [
            'date' => ['required', 'string', 'filled'],
            'time_am' => ['nullable', 'regex:/^\d{2}:\d{2}(:\d{2})?$/'],
            'time_pm' => ['nullable', 'regex:/^\d{2}:\d{2}(:\d{2})?$/'],
            'note' => 'nullable|string|max:5000',
        ];
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    private function notePayload(array $validated): array
    {
        return [
            'date' => $validated['date']->toDateString(),
            'time_am' => $validated['time_am'] ?? null,
            'time_pm' => $validated['time_pm'] ?? null,
            'note' => $validated['note'] ?? null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function formatNote(NurseNote $note): array
    {
        return [
            'id' => $note->id,
            'date' => $note->date ? verta($note->date)->format('Y/m/d') : null,
            'time_am' => $note->time_am ? $note->time_am->format('H:i') : null,
            'time_pm' => $note->time_pm ? $note->time_pm->format('H:i') : null,
            'note' => $note->note,
            'nurse_name' => $note->nurse?->full_name,
            'created_by_name' => $note->createdBy?->name,
        ];
    }
}
