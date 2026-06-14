<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Controllers\V1\Concerns\ManagesUnderReviewWorkflow;
use App\Http\Controllers\V1\Concerns\PaginatesInertiaIndex;
use App\Models\Bed;
use App\Models\Department;
use App\Models\MedicationAdministrationRecord;
use App\Models\Room;
use App\Models\UnderReview;
use App\Models\Visit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class UnderReviewController extends Controller
{
    use ManagesUnderReviewWorkflow;
    use PaginatesInertiaIndex;

    protected function authorizeMenu(): void
    {
        abort_unless(request()->user()?->can('show-under-review-menu'), 403);
    }

    protected function branchId(): int
    {
        return (int) request()->user()->branch_id;
    }

    protected function ensureAccessible(UnderReview $underReview): void
    {
        abort_unless($underReview->userCanView(request()->user()), 404);
    }

    protected function scopedDepartmentId(): ?int
    {
        $user = request()->user();
        if (UnderReview::userBypassesDepartmentScope($user)) {
            return null;
        }

        return $user?->department_id;
    }

    private function indexFilterOptions(int $branchId): array
    {
        $departmentId = $this->scopedDepartmentId();

        return [
            'rooms' => Room::query()
                ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
                ->when($departmentId, fn ($q) => $q->where('department_id', $departmentId))
                ->orderBy('name')
                ->get(['id', 'name']),
            'departments' => Department::query()
                ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
                ->when($departmentId, fn ($q) => $q->whereKey($departmentId))
                ->orderBy('name')
                ->get(['id', 'name']),
        ];
    }

    public function index(Request $request): Response
    {
        $this->authorizeMenu();

        $query = $this->underReviewBaseQuery()
            ->where('is_discharged', '0')
            ->orderByDesc('created_at');

        $this->applyUnderReviewListFilters($query, $request);

        $branchId = $this->branchId();
        $user = $request->user();

        $paginator = $this->paginateQuery($query, $request, 25, [10, 15, 25, 50, 100]);
        $items = $this->paginationPayload(
            $paginator,
            fn (UnderReview $item) => $this->transformUnderReviewListItem($item, $user),
        );

        return Inertia::render('UnderReviews/Index', [
            'underReviews' => $items,
            'activeTab' => 'index',
            'filters' => $this->indexFiltersFromRequest($request),
            'filterOptions' => $this->indexFilterOptions($branchId),
            'urls' => $this->underReviewWorkflowUrls(),
        ]);
    }

    public function pending(Request $request): Response
    {
        $this->authorizeMenu();

        $user = $request->user();
        $query = $this->underReviewBaseQuery()
            ->where('is_discharged', '0')
            ->whereNull('processed_by')
            ->orderByDesc('created_at');

        $this->applyUnderReviewListFilters($query, $request);

        $paginator = $this->paginateQuery($query, $request, 25, [10, 15, 25, 50, 100]);
        $items = $this->paginationPayload(
            $paginator,
            fn (UnderReview $item) => $this->transformUnderReviewListItem($item, $user, true),
        );

        return Inertia::render('UnderReviews/Pending', [
            'underReviews' => $items,
            'activeTab' => 'pending',
            'filters' => $this->workflowFiltersFromRequest($request),
            'urls' => $this->underReviewWorkflowUrls(),
        ]);
    }

    public function myCases(Request $request): Response
    {
        $this->authorizeMenu();

        $user = $request->user();
        $query = $this->underReviewBaseQuery()
            ->where('is_discharged', '0')
            ->where('processed_by', $user->id)
            ->orderByDesc('created_at');

        $this->applyUnderReviewListFilters($query, $request);

        $paginator = $this->paginateQuery($query, $request, 25, [10, 15, 25, 50, 100]);
        $items = $this->paginationPayload(
            $paginator,
            fn (UnderReview $item) => $this->transformUnderReviewListItem($item, $user),
        );

        return Inertia::render('UnderReviews/MyCases', [
            'underReviews' => $items,
            'activeTab' => 'myCases',
            'filters' => $this->workflowFiltersFromRequest($request),
            'urls' => $this->underReviewWorkflowUrls(),
        ]);
    }

    public function discharged(Request $request): Response
    {
        $this->authorizeMenu();

        $user = $request->user();
        $query = $this->underReviewBaseQuery()
            ->where('is_discharged', '1')
            ->orderByDesc('updated_at');

        $this->applyUnderReviewListFilters($query, $request);

        $branchId = $this->branchId();
        $paginator = $this->paginateQuery($query, $request, 25, [10, 15, 25, 50, 100]);
        $items = $this->paginationPayload(
            $paginator,
            fn (UnderReview $item) => $this->transformUnderReviewListItem($item, $user),
        );

        return Inertia::render('UnderReviews/Discharged', [
            'underReviews' => $items,
            'activeTab' => 'discharged',
            'filters' => $this->indexFiltersFromRequest($request),
            'filterOptions' => $this->indexFilterOptions($branchId),
            'urls' => $this->underReviewWorkflowUrls(),
        ]);
    }

    public function accept(Request $request, UnderReview $underReview): RedirectResponse
    {
        $this->authorizeMenu();
        $this->ensureAccessible($underReview);
        $this->authorize('accept', $underReview);

        $underReview->update([
            'processed_by' => $request->user()->id,
        ]);

        return redirect()
            ->route('react.under-reviews.my-cases')
            ->with('success', localize('global.appointment_accepted_successfully'));
    }

    /**
     * @return array<string, string>
     */
    private function indexFiltersFromRequest(Request $request): array
    {
        return [
            'patient_name' => (string) $request->input('patient_name', ''),
            'id_card' => (string) $request->input('id_card', ''),
            'father_name' => (string) $request->input('father_name', ''),
            'room_id' => (string) $request->input('room_id', ''),
            'department_id' => (string) $request->input('department_id', ''),
        ];
    }

    /**
     * @return array<string, string>
     */
    private function workflowFiltersFromRequest(Request $request): array
    {
        return [
            'search' => (string) $request->input('search', ''),
            'record_id' => (string) $request->input('record_id', ''),
            'patient_id' => (string) $request->input('patient_id', ''),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function show(Request $request, UnderReview $underReview): Response
    {
        $this->authorizeMenu();
        $this->ensureAccessible($underReview);

        $underReview->load([
            'patient:id,name,father_name,id_card,phone',
            'doctor:id,name',
            'department:id,name',
            'room:id,name',
            'bed:id,number',
            'processedBy:id,name,last_name',
            'appointment:id,patient_id,doctor_id,is_completed',
            'visits.doctor:id,name',
            'hospitalization:id,under_review_id,is_discharged',
            'nursingAssessments:id,morphable_id,morphable_type,created_at',
        ]);

        $medicationRecords = MedicationAdministrationRecord::query()
            ->where('morphable_type', UnderReview::class)
            ->where('morphable_id', $underReview->id)
            ->with(['medicine:id,name', 'nurse:id,first_name,last_name'])
            ->orderByDesc('order_date')
            ->orderByDesc('created_at')
            ->limit(50)
            ->get();

        $user = $request->user();
        $accepted = (bool) $underReview->processed_by;

        return Inertia::render('UnderReviews/Show', [
            'underReview' => $this->transformDetail($underReview, $medicationRecords),
            'permissions' => [
                'accept' => $user->can('accept', $underReview),
                'discharge' => $user->can('complete', $underReview),
                'store_visit' => ! (bool) $underReview->is_discharged && $accepted,
                'edit_visit' => $user->can('edit-under-review-visit'),
                'delete_visit' => $user->can('delete-under-review-visit'),
            ],
            'sectionPermissions' => [
                'prescription' => $accepted && $user->can('show-prescriptions-menu') && (bool) $underReview->appointment_id,
                'lab' => $accepted && $user->can('show-labs-menu') && (bool) $underReview->appointment_id,
                'blood' => $accepted && $user->can('show-blood-request-menu') && (bool) $underReview->appointment_id,
                'physiotherapy' => $accepted && $user->can('show-physiotherapy-procedures') && (bool) $underReview->appointment_id,
                'hospitalization' => $accepted && $user->can('show-hospitalizations-menu') && (bool) $underReview->appointment_id,
            ],
            'urls' => [
                'index' => route('react.under-reviews.index'),
                'pending' => route('react.under-reviews.pending'),
                'myCases' => route('react.under-reviews.my-cases'),
                'discharged' => route('react.under-reviews.discharged'),
                'accept' => route('react.under-reviews.accept', $underReview),
                'discharge' => route('react.under-reviews.discharge', $underReview),
                'visit_store' => route('react.under-reviews.visits.store', $underReview),
                'visit_update' => url('/react/under-reviews/'.$underReview->id.'/visits'),
            ],
        ]);
    }

    public function edit(UnderReview $underReview): Response
    {
        $this->authorizeMenu();
        $this->ensureAccessible($underReview);
        abort_unless(request()->user()->can('edit-under-reviews'), 403);

        $underReview->load(['appointment:id,patient_id', 'room:id,name,department_id', 'bed:id,number']);

        $branchId = $underReview->branch_id ?? request()->user()->branch_id;
        $departmentId = $this->scopedDepartmentId();

        return Inertia::render('UnderReviews/Edit', [
            'underReview' => [
                'id' => $underReview->id,
                'reason' => $underReview->reason,
                'remarks' => $underReview->remarks,
                'department_id' => $underReview->department_id,
                'room_id' => $underReview->room_id,
                'bed_id' => $underReview->bed_id,
                'patient_id' => $underReview->patient_id,
                'appointment_id' => $underReview->appointment_id,
                'branch_id' => $underReview->branch_id,
            ],
            'departments' => Department::query()
                ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
                ->when($departmentId, fn ($q) => $q->whereKey($departmentId))
                ->orderBy('name')
                ->get(['id', 'name']),
            'rooms' => Room::query()
                ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
                ->when($departmentId, fn ($q) => $q->where('department_id', $departmentId))
                ->orderBy('name')
                ->get(['id', 'name', 'department_id']),
            'beds' => Bed::query()
                ->when($branchId, fn ($q) => $q->whereHas('room', fn ($room) => $room->where('branch_id', $branchId)))
                ->orderBy('number')
                ->get(['id', 'number', 'room_id', 'is_occupied']),
            'urls' => [
                'show' => route('react.under-reviews.show', $underReview),
                'update' => route('react.under-reviews.update', $underReview),
            ],
        ]);
    }

    public function update(Request $request, UnderReview $underReview): RedirectResponse
    {
        $this->authorizeMenu();
        $this->ensureAccessible($underReview);
        abort_unless($request->user()->can('edit-under-reviews'), 403);

        $validated = $request->validate([
            'reason' => 'required|string',
            'remarks' => 'required|string',
            'department_id' => 'required|exists:departments,id',
            'room_id' => 'required|exists:rooms,id',
            'bed_id' => 'required|exists:beds,id',
            'patient_id' => 'required|exists:patients,id',
            'appointment_id' => 'required|exists:appointments,id',
            'branch_id' => 'required|integer',
            'is_discharged' => 'nullable',
            'discharge_remark' => 'nullable',
            'operation_id' => 'nullable',
        ]);

        $room = Room::findOrFail($validated['room_id']);
        abort_unless(
            $room->department_id === null || (int) $room->department_id === (int) $validated['department_id'],
            422
        );

        if (! UnderReview::userBypassesDepartmentScope($request->user())) {
            abort_unless(
                (int) $validated['department_id'] === (int) $request->user()->department_id,
                403
            );
        }

        $bed = Bed::findOrFail($validated['bed_id']);
        abort_unless((int) $bed->room_id === (int) $validated['room_id'], 422);

        $appointment = \App\Models\Appointment::findOrFail($validated['appointment_id']);
        $validated['doctor_id'] = $appointment->doctor_id;

        if ((int) $validated['bed_id'] !== (int) $underReview->bed_id) {
            $this->releaseBed($underReview->bed_id);
            Bed::query()->whereKey($validated['bed_id'])->update(['is_occupied' => 1]);
        }

        $underReview->update($validated);

        return redirect()
            ->route('react.under-reviews.show', $underReview)
            ->with('success', localize('global.advice_updated_successfully.'));
    }

    public function discharge(Request $request, UnderReview $underReview): RedirectResponse
    {
        $this->authorizeMenu();
        $this->ensureAccessible($underReview);
        $this->authorize('complete', $underReview);

        $validated = $request->validate([
            'discharge_remark' => 'required|string',
        ]);

        DB::transaction(function () use ($underReview, $validated) {
            $underReview->update([
                'is_discharged' => 1,
                'discharge_remark' => $validated['discharge_remark'],
            ]);
        });

        return redirect()
            ->route('react.under-reviews.discharged')
            ->with('success', localize('global.under_review_updated_successfully.'));
    }

    public function storeVisit(Request $request, UnderReview $underReview): RedirectResponse
    {
        $this->authorizeMenu();
        $this->ensureAccessible($underReview);
        abort_if((bool) $underReview->is_discharged, 403);
        abort_unless((bool) $underReview->processed_by, 403);

        $validated = $request->validate([
            'description' => 'required|string',
        ]);

        Visit::create([
            'description' => $validated['description'],
            'patient_id' => $underReview->patient_id,
            'doctor_id' => $underReview->doctor_id,
            'under_review_id' => $underReview->id,
        ]);

        return redirect()
            ->route('react.under-reviews.show', $underReview)
            ->with('success', localize('global.visit_created_successfully.'));
    }

    public function updateVisit(Request $request, UnderReview $underReview, Visit $visit): RedirectResponse
    {
        $this->authorizeMenu();
        $this->ensureAccessible($underReview);
        abort_unless($request->user()->can('edit-under-review-visit'), 403);
        abort_unless((int) $visit->under_review_id === (int) $underReview->id, 404);

        $validated = $request->validate([
            'description' => 'required|string',
        ]);

        $visit->update($validated);

        return redirect()
            ->route('react.under-reviews.show', $underReview)
            ->with('success', localize('global.visit_updated_successfully.'));
    }

    public function destroyVisit(UnderReview $underReview, Visit $visit): RedirectResponse
    {
        $this->authorizeMenu();
        $this->ensureAccessible($underReview);
        abort_unless(request()->user()->can('delete-under-review-visit'), 403);
        abort_unless((int) $visit->under_review_id === (int) $underReview->id, 404);

        $visit->delete();

        return redirect()
            ->route('react.under-reviews.show', $underReview)
            ->with('success', localize('global.visit_deleted_successfully.'));
    }

    /**
     * @param  \Illuminate\Support\Collection<int, MedicationAdministrationRecord>  $medicationRecords
     * @return array<string, mixed>
     */
    private function transformDetail(UnderReview $underReview, $medicationRecords): array
    {
        return [
            'id' => $underReview->id,
            'reason' => $underReview->reason,
            'remarks' => $underReview->remarks,
            'discharge_remark' => $underReview->discharge_remark,
            'is_discharged' => (bool) $underReview->is_discharged,
            'is_accepted' => (bool) $underReview->processed_by,
            'processed_by_name' => $underReview->processedBy
                ? trim($underReview->processedBy->name.' '.$underReview->processedBy->last_name)
                : null,
            'admission_date' => $this->formatDate($underReview->created_at),
            'admission_time' => $underReview->created_at?->format('H:i'),
            'appointment_id' => $underReview->appointment_id,
            'patient' => $underReview->patient ? [
                'id' => $underReview->patient->id,
                'name' => $underReview->patient->name,
                'father_name' => $underReview->patient->father_name,
                'id_card' => $underReview->patient->id_card,
                'phone' => $underReview->patient->phone,
            ] : null,
            'doctor_name' => $underReview->doctor?->name,
            'department_name' => $underReview->department?->name,
            'room_name' => $underReview->room?->name,
            'bed_number' => $underReview->bed?->number,
            'visits' => $underReview->visits->map(fn (Visit $visit) => [
                'id' => $visit->id,
                'description' => $visit->description,
                'doctor_name' => $visit->doctor?->name,
            ])->values()->all(),
            'medication_records' => $medicationRecords->map(fn (MedicationAdministrationRecord $record) => [
                'id' => $record->id,
                'order_date' => $record->order_date,
                'medicine_name' => $record->medicine?->name,
                'nurse_name' => $record->nurse
                    ? trim($record->nurse->first_name.' '.$record->nurse->last_name)
                    : null,
            ])->values()->all(),
            'nursing_assessments_count' => $underReview->nursingAssessments->count(),
            'hospitalizations_count' => $underReview->hospitalization->count(),
        ];
    }

    private function releaseBed(?int $bedId): void
    {
        if (! $bedId) {
            return;
        }

        Bed::query()->whereKey($bedId)->update(['is_occupied' => 0]);
    }

    private function formatDate(?\Illuminate\Support\Carbon $date): ?string
    {
        if (! $date) {
            return null;
        }

        try {
            return verta($date)->format('Y/n/j');
        } catch (\Throwable) {
            return $date->format('Y-m-d');
        }
    }
}
