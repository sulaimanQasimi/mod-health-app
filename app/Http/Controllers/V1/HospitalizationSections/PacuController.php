<?php

namespace App\Http\Controllers\V1\HospitalizationSections;

use App\Http\Controllers\Controller;
use App\Jobs\SendNewPACUNotification;
use App\Models\Bed;
use App\Models\Department;
use App\Models\Hospitalization;
use App\Models\PACU;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PacuController extends Controller
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
                    ],
                ],
            ]);
        }

        $items = $hospitalization->pacus()
            ->with(['patient:id,name'])
            ->latest()
            ->get()
            ->map(fn (PACU $item) => $this->formatListItem($item))
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

        $hospitalization->loadMissing(['patient:id,name', 'room:id,name', 'bed:id,number']);
        $branchId = $hospitalization->branch_id ?? request()->user()->branch_id;

        return response()->json([
            'success' => true,
            'data' => [
                'patient_name' => $hospitalization->patient?->name,
                'current_room_name' => $hospitalization->room?->name,
                'current_bed_number' => $hospitalization->bed?->number,
                'default_department_id' => $hospitalization->department_id ?? request()->user()->department_id,
                'departments' => $this->departments($branchId),
            ],
        ]);
    }

    public function store(Request $request, Hospitalization $hospitalization): JsonResponse
    {
        $this->ensureAccessible($hospitalization);
        abort_if((bool) $hospitalization->is_discharged, 403);
        abort_unless($this->canCreate($request->user(), $hospitalization), 403);

        $hospitalization->loadMissing(['patient:id,name', 'appointment:id,doctor_id']);

        $validated = $request->validate([
            'description' => 'required|string|max:2000',
            'department_id' => 'required|exists:departments,id',
        ]);

        DB::transaction(function () use ($validated, $hospitalization, $request) {
            $pacu = PACU::create([
                'description' => $validated['description'],
                'patient_id' => $hospitalization->patient_id,
                'appointment_id' => $hospitalization->appointment_id,
                'hospitalization_id' => $hospitalization->id,
                'department_id' => $validated['department_id'],
                'branch_id' => $hospitalization->branch_id ?? $request->user()->branch_id,
                'status' => 'new',
            ]);

            $this->clearHospitalizationBed($hospitalization);

            SendNewPACUNotification::dispatch($request->user()->id, $pacu->id);
        });

        return response()->json(['success' => true]);
    }

    private function clearHospitalizationBed(Hospitalization $hospitalization): void
    {
        if ($hospitalization->bed_id) {
            Bed::query()
                ->where('id', $hospitalization->bed_id)
                ->update(['is_occupied' => false]);
        }

        $hospitalization->update([
            'room_id' => null,
            'bed_id' => null,
        ]);
    }

    public function destroy(Hospitalization $hospitalization, PACU $pacu): JsonResponse
    {
        $this->ensureAccessible($hospitalization);
        abort_if((bool) $hospitalization->is_discharged, 403);
        abort_unless(request()->user()->can('show-pacu-menu'), 403);
        abort_unless((int) $pacu->hospitalization_id === (int) $hospitalization->id, 404);

        $pacu->delete();

        return response()->json(['success' => true]);
    }

    private function ensureAccessible(Hospitalization $hospitalization): void
    {
        abort_unless($hospitalization->userCanView(request()->user()), 404);
    }

    private function canView($user): bool
    {
        return ($user?->can('refer-to-pacu') ?? false)
            || ($user?->can('show-pacu-menu') ?? false);
    }

    private function canCreate($user, Hospitalization $hospitalization): bool
    {
        return ! (bool) $hospitalization->is_discharged && ($user?->can('refer-to-pacu') ?? false);
    }

    /**
     * @return array{view: bool, create: bool, edit: bool, delete: bool}
     */
    private function permissions($user, Hospitalization $hospitalization): array
    {
        return [
            'view' => $this->canView($user),
            'create' => $this->canCreate($user, $hospitalization),
            'edit' => ! (bool) $hospitalization->is_discharged && ($user?->can('show-pacu-menu') ?? false),
            'delete' => ! (bool) $hospitalization->is_discharged && ($user?->can('show-pacu-menu') ?? false),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function formatListItem(PACU $pacu): array
    {
        return [
            'id' => $pacu->id,
            'patient_name' => $pacu->patient?->name,
            'description' => $pacu->description,
            'status' => $pacu->status,
            'created_at' => $this->formatDate($pacu->created_at),
            'urls' => [
                'show' => route('pacus.show', $pacu),
            ],
        ];
    }

    protected function formatDate(\Illuminate\Support\Carbon|string|null $date): ?string
    {
        if (! $date) {
            return null;
        }

        if (! $date instanceof \Illuminate\Support\Carbon) {
            try {
                $date = \Illuminate\Support\Carbon::parse($date);
            } catch (\Throwable) {
                return null;
            }
        }

        try {
            return verta($date)->format('Y/n/j');
        } catch (\Throwable) {
            return $date->format('Y-m-d');
        }
    }

    /**
     * @return list<array{id: int, name: string}>
     */
    private function departments(?int $branchId): array
    {
        return Department::query()
            ->when($branchId, fn ($query) => $query->where('branch_id', $branchId))
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (Department $department) => ['id' => $department->id, 'name' => $department->name])
            ->values()
            ->all();
    }
}
