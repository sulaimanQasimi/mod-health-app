<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Controllers\V1\Concerns\PaginatesInertiaIndex;
use App\Models\Bed;
use App\Models\DiabetesChart;
use App\Models\MedicationAdministrationRecord;
use App\Models\NurseNote;
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
    use PaginatesInertiaIndex;

    protected function authorizeMenu(): void
    {
        abort_unless(request()->user()?->can('show-under-review-menu'), 403);
    }

    protected function branchId(): int
    {
        return (int) request()->user()->branch_id;
    }

    protected function ensureBranch(UnderReview $underReview): void
    {
        abort_unless((int) $underReview->branch_id === $this->branchId(), 404);
    }

    public function index(Request $request): Response
    {
        $this->authorizeMenu();

        $query = UnderReview::query()
            ->where('branch_id', $this->branchId())
            ->where('is_discharged', '0')
            ->with([
                'patient:id,name,father_name,id_card',
                'room:id,name',
                'bed:id,number',
            ])
            ->orderByDesc('created_at');

        if ($request->filled('q')) {
            $search = $request->q;
            $query->where(function ($w) use ($search) {
                $w->whereHas('patient', function ($p) use ($search) {
                    $p->where('name', 'like', '%'.$search.'%')
                        ->orWhere('father_name', 'like', '%'.$search.'%')
                        ->orWhere('id_card', 'like', '%'.$search.'%');
                });
            });
        }

        $paginator = $this->paginateQuery($query, $request, 25, [10, 15, 25, 50, 100]);
        $items = $this->paginationPayload($paginator, fn (UnderReview $item) => [
            'id' => $item->id,
            'patient_id_card' => $item->patient?->id_card,
            'patient_name' => $item->patient?->name,
            'father_name' => $item->patient?->father_name,
            'room_name' => $item->room?->name,
            'bed_number' => $item->bed?->number,
            'admission_date' => $this->formatDate($item->created_at),
            'urls' => [
                'show' => route('react.under-reviews.show', $item),
            ],
        ]);

        return Inertia::render('UnderReviews/Index', [
            'underReviews' => $items,
            'filters' => ['q' => (string) $request->input('q', '')],
            'urls' => [
                'current' => route('react.under-reviews.index'),
                'show' => url('/react/under-reviews'),
            ],
        ]);
    }

    public function show(Request $request, UnderReview $underReview): Response
    {
        $this->authorizeMenu();
        $this->ensureBranch($underReview);

        $underReview->load([
            'patient:id,name,father_name,id_card,phone',
            'doctor:id,name',
            'room:id,name',
            'bed:id,number',
            'appointment:id,patient_id,doctor_id,is_completed',
            'visits.doctor:id,name',
            'hospitalization:id,under_review_id,is_discharged',
            'vitalSigns.vitalSignType:id,name',
            'vitalSigns.schedules.nurse:id,first_name,last_name',
            'nutritionCares.createdBy:id,name,last_name',
            'nutritionCares.nurse:id,first_name,last_name',
            'nursingAssessments:id,morphable_id,morphable_type,created_at',
        ]);

        $diabetesCharts = DiabetesChart::query()
            ->where('diabetes_chartable_type', UnderReview::class)
            ->where('diabetes_chartable_id', $underReview->id)
            ->with(['nurse:id,first_name,last_name', 'medicine:id,name'])
            ->orderByDesc('date')
            ->orderByDesc('time')
            ->limit(50)
            ->get();

        $nurseNotes = NurseNote::query()
            ->where('morphable_type', UnderReview::class)
            ->where('morphable_id', $underReview->id)
            ->with(['nurse:id,first_name,last_name'])
            ->orderByDesc('date')
            ->orderByDesc('created_at')
            ->limit(50)
            ->get();

        $medicationRecords = MedicationAdministrationRecord::query()
            ->where('morphable_type', UnderReview::class)
            ->where('morphable_id', $underReview->id)
            ->with(['medicine:id,name', 'nurse:id,first_name,last_name'])
            ->orderByDesc('order_date')
            ->orderByDesc('created_at')
            ->limit(50)
            ->get();

        $user = $request->user();

        return Inertia::render('UnderReviews/Show', [
            'underReview' => $this->transformDetail($underReview, $diabetesCharts, $nurseNotes, $medicationRecords),
            'permissions' => [
                'edit' => $user->can('edit-under-reviews'),
                'discharge' => ! $underReview->discharge_remark && $user->can('edit-under-reviews'),
                'store_visit' => ! (bool) $underReview->is_discharged,
                'edit_visit' => $user->can('edit-under-review-visit'),
                'delete_visit' => $user->can('delete-under-review-visit'),
            ],
            'sectionPermissions' => [
                'prescription' => $user->can('show-prescriptions-menu'),
                'lab' => $user->can('show-labs-menu'),
            ],
            'urls' => [
                'index' => route('react.under-reviews.index'),
                'edit' => route('react.under-reviews.edit', $underReview),
                'discharge' => route('react.under-reviews.discharge', $underReview),
                'visit_store' => route('react.under-reviews.visits.store', $underReview),
                'visit_update' => url('/react/under-reviews/'.$underReview->id.'/visits'),
                'appointment' => $underReview->appointment_id
                    ? route('react.appointments.show', $underReview->appointment_id)
                    : null,
            ],
        ]);
    }

    public function edit(UnderReview $underReview): Response
    {
        $this->authorizeMenu();
        $this->ensureBranch($underReview);
        abort_unless(request()->user()->can('edit-under-reviews'), 403);

        $underReview->load(['appointment:id,patient_id', 'room:id,name', 'bed:id,number']);

        return Inertia::render('UnderReviews/Edit', [
            'underReview' => [
                'id' => $underReview->id,
                'reason' => $underReview->reason,
                'remarks' => $underReview->remarks,
                'room_id' => $underReview->room_id,
                'bed_id' => $underReview->bed_id,
                'patient_id' => $underReview->patient_id,
                'appointment_id' => $underReview->appointment_id,
                'branch_id' => $underReview->branch_id,
            ],
            'rooms' => Room::query()->orderBy('name')->get(['id', 'name']),
            'beds' => Bed::query()->orderBy('number')->get(['id', 'number', 'room_id', 'is_occupied']),
            'urls' => [
                'show' => route('react.under-reviews.show', $underReview),
                'update' => route('react.under-reviews.update', $underReview),
            ],
        ]);
    }

    public function update(Request $request, UnderReview $underReview): RedirectResponse
    {
        $this->authorizeMenu();
        $this->ensureBranch($underReview);
        abort_unless($request->user()->can('edit-under-reviews'), 403);

        $validated = $request->validate([
            'reason' => 'required|string',
            'remarks' => 'required|string',
            'room_id' => 'required|exists:rooms,id',
            'bed_id' => 'required|exists:beds,id',
            'patient_id' => 'required|exists:patients,id',
            'appointment_id' => 'required|exists:appointments,id',
            'branch_id' => 'required|integer',
            'is_discharged' => 'nullable',
            'discharge_remark' => 'nullable',
            'operation_id' => 'nullable',
        ]);

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
        $this->ensureBranch($underReview);
        abort_unless($request->user()->can('edit-under-reviews'), 403);
        abort_if((bool) $underReview->is_discharged, 403);

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
            ->route('react.under-reviews.show', $underReview)
            ->with('success', localize('global.under_review_updated_successfully.'));
    }

    public function storeVisit(Request $request, UnderReview $underReview): RedirectResponse
    {
        $this->authorizeMenu();
        $this->ensureBranch($underReview);
        abort_if((bool) $underReview->is_discharged, 403);

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
        $this->ensureBranch($underReview);
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
        $this->ensureBranch($underReview);
        abort_unless(request()->user()->can('delete-under-review-visit'), 403);
        abort_unless((int) $visit->under_review_id === (int) $underReview->id, 404);

        $visit->delete();

        return redirect()
            ->route('react.under-reviews.show', $underReview)
            ->with('success', localize('global.visit_deleted_successfully.'));
    }

    /**
     * @param  \Illuminate\Support\Collection<int, DiabetesChart>  $diabetesCharts
     * @param  \Illuminate\Support\Collection<int, NurseNote>  $nurseNotes
     * @param  \Illuminate\Support\Collection<int, MedicationAdministrationRecord>  $medicationRecords
     * @return array<string, mixed>
     */
    private function transformDetail(
        UnderReview $underReview,
        $diabetesCharts,
        $nurseNotes,
        $medicationRecords,
    ): array {
        return [
            'id' => $underReview->id,
            'reason' => $underReview->reason,
            'remarks' => $underReview->remarks,
            'discharge_remark' => $underReview->discharge_remark,
            'is_discharged' => (bool) $underReview->is_discharged,
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
            'room_name' => $underReview->room?->name,
            'bed_number' => $underReview->bed?->number,
            'visits' => $underReview->visits->map(fn (Visit $visit) => [
                'id' => $visit->id,
                'description' => $visit->description,
                'doctor_name' => $visit->doctor?->name,
            ])->values()->all(),
            'diabetes_charts' => $diabetesCharts->map(fn (DiabetesChart $chart) => [
                'id' => $chart->id,
                'date' => $chart->date,
                'time' => $chart->time,
                'rbs' => $chart->rbs,
                'fbs' => $chart->fbs,
                'insulin_dose' => $chart->insulin_dose,
                'nurse_name' => $chart->nurse
                    ? trim($chart->nurse->first_name.' '.$chart->nurse->last_name)
                    : null,
                'medicine_name' => $chart->medicine?->name,
            ])->values()->all(),
            'nurse_notes' => $nurseNotes->map(fn (NurseNote $note) => [
                'id' => $note->id,
                'date' => $note->date,
                'note_am' => $note->note_am,
                'note_pm' => $note->note_pm,
                'nurse_name' => $note->nurse
                    ? trim($note->nurse->first_name.' '.$note->nurse->last_name)
                    : null,
            ])->values()->all(),
            'medication_records' => $medicationRecords->map(fn (MedicationAdministrationRecord $record) => [
                'id' => $record->id,
                'order_date' => $record->order_date,
                'medicine_name' => $record->medicine?->name,
                'nurse_name' => $record->nurse
                    ? trim($record->nurse->first_name.' '.$record->nurse->last_name)
                    : null,
            ])->values()->all(),
            'vital_signs' => $underReview->vitalSigns->map(fn ($vital) => [
                'id' => $vital->id,
                'type_name' => $vital->vitalSignType?->name,
                'schedules_count' => $vital->schedules->count(),
                'recorded_at' => $this->formatDate($vital->created_at),
            ])->values()->all(),
            'nutrition_cares' => $underReview->nutritionCares->map(fn ($care) => [
                'id' => $care->id,
                'date' => $care->date,
                'nurse_name' => $care->nurse
                    ? trim($care->nurse->first_name.' '.$care->nurse->last_name)
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
