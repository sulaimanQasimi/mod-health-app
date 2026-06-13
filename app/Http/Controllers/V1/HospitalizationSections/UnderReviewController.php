<?php

namespace App\Http\Controllers\V1\HospitalizationSections;

use App\Http\Controllers\Controller;
use App\Models\Bed;
use App\Models\Hospitalization;
use App\Models\Room;
use App\Models\UnderReview;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UnderReviewController extends Controller
{
    public function meta(Hospitalization $hospitalization): JsonResponse
    {
        $this->ensureAccessible($hospitalization);
        abort_unless($this->canCreate(request()->user(), $hospitalization), 403);

        $branchId = $hospitalization->branch_id ?? request()->user()->branch_id;

        return response()->json([
            'success' => true,
            'data' => [
                'rooms' => Room::query()
                    ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
                    ->orderBy('name')
                    ->get(['id', 'name']),
                'beds' => Bed::query()
                    ->when($branchId, fn ($q) => $q->whereHas('room', fn ($room) => $room->where('branch_id', $branchId)))
                    ->orderBy('number')
                    ->get(['id', 'number', 'room_id', 'is_occupied']),
            ],
        ]);
    }

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

        $items = UnderReview::query()
            ->where('hospitalization_id', $hospitalization->id)
            ->with(['room:id,name', 'bed:id,number'])
            ->latest()
            ->get()
            ->map(fn (UnderReview $item) => [
                'id' => $item->id,
                'reason' => $item->reason,
                'remarks' => $item->remarks,
                'room_name' => $item->room?->name,
                'bed_number' => $item->bed?->number,
                'is_active' => ! (bool) $item->is_discharged,
                'urls' => [
                    'show' => route('react.under-reviews.show', $item->id),
                    'edit' => route('react.under-reviews.edit', $item->id),
                ],
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
        abort_unless($this->canCreate($request->user(), $hospitalization), 403);
        abort_unless((bool) $hospitalization->appointment_id, 422);

        $validated = $request->validate([
            'reason' => 'required|string',
            'remarks' => 'required|string',
            'room_id' => 'required|exists:rooms,id',
            'bed_id' => 'required|exists:beds,id',
        ]);

        $bed = Bed::findOrFail($validated['bed_id']);
        abort_if((bool) $bed->is_occupied, 422);

        $hospitalization->loadMissing(['appointment:id,doctor_id']);

        DB::transaction(function () use ($validated, $hospitalization, $request, $bed) {
            $bed->update(['is_occupied' => true]);

            $underReview = UnderReview::create([
                'reason' => $validated['reason'],
                'remarks' => $validated['remarks'],
                'room_id' => $validated['room_id'],
                'bed_id' => $validated['bed_id'],
                'patient_id' => $hospitalization->patient_id,
                'appointment_id' => $hospitalization->appointment_id,
                'doctor_id' => $hospitalization->doctor_id ?? $hospitalization->appointment?->doctor_id,
                'hospitalization_id' => $hospitalization->id,
                'branch_id' => $hospitalization->branch_id ?? $request->user()->branch_id,
                'is_discharged' => 0,
            ]);

            $this->clearHospitalizationBed($hospitalization);

            $hospitalization->update(['under_review_id' => $underReview->id]);
        });

        return response()->json(['success' => true]);
    }

    public function destroy(Hospitalization $hospitalization, UnderReview $underReview): JsonResponse
    {
        $this->ensureAccessible($hospitalization);
        abort_unless(request()->user()->can('delete-under-reviews'), 403);
        abort_unless((int) $underReview->hospitalization_id === (int) $hospitalization->id, 404);

        $underReview->delete();

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

    private function ensureAccessible(Hospitalization $hospitalization): void
    {
        abort_unless($hospitalization->userCanView(request()->user()), 404);
    }

    private function canView($user): bool
    {
        return ($user?->can('patient-under-review') ?? false)
            || ($user?->can('show-under-review-menu') ?? false);
    }

    private function canCreate($user, Hospitalization $hospitalization): bool
    {
        return ! (bool) $hospitalization->is_discharged
            && (bool) $hospitalization->appointment_id
            && ($user?->can('patient-under-review') ?? false);
    }

    /**
     * @return array{view: bool, create: bool, edit: bool, delete: bool}
     */
    private function permissions($user, Hospitalization $hospitalization): array
    {
        return [
            'view' => $this->canView($user),
            'create' => $this->canCreate($user, $hospitalization),
            'edit' => $user?->can('edit-under-reviews') ?? false,
            'delete' => $user?->can('delete-under-reviews') ?? false,
        ];
    }
}
