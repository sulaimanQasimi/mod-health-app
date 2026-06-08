<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Controllers\V1\Concerns\ManagesPhysiotherapyProcedureListing;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\PhysiotherapyProcedure;
use App\Models\PhysiotherapyProcedureReview;
use App\Models\PhysiotherapyType;
use Hekmatinasser\Verta\Facades\Verta;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class PhysiotherapyProcedureController extends Controller
{
    use ManagesPhysiotherapyProcedureListing;

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', PhysiotherapyProcedure::class);

        return $this->renderList($request, 'all');
    }

    public function myProcedures(Request $request): Response
    {
        $this->authorize('viewOwn', PhysiotherapyProcedure::class);

        return $this->renderList($request, 'own');
    }

    public function show(Request $request, PhysiotherapyProcedure $physiotherapyProcedure): Response
    {
        $this->authorizeProcedureAccess($request->user(), $physiotherapyProcedure);

        $physiotherapyProcedure->load([
            'appointment.patient:id,name,last_name,father_name,id_card,phone',
            'physiotherapyType:id,name',
            'doctor:id,name,user_id',
            'createdBy:id,name,last_name',
            'updatedBy:id,name,last_name',
            'reviews.createdBy:id,name,last_name',
            'reviews.updatedBy:id,name,last_name',
        ]);

        $user = $request->user();
        $formOptions = null;
        if ($user->can('edit-physiotherapy-procedures')) {
            $formOptions = [
                'physiotherapy_types' => PhysiotherapyType::query()->orderBy('name')->get(['id', 'name']),
                'physiotherapists' => $this->physiotherapistDoctorsForFilters()->map(fn (Doctor $doctor) => [
                    'id' => $doctor->id,
                    'name' => $doctor->name,
                ])->values()->all(),
            ];
        }

        return Inertia::render('PhysiotherapyProcedures/Show', [
            'procedure' => $this->transformDetail($physiotherapyProcedure),
            'formOptions' => $formOptions,
            'permissions' => $this->showPermissions($user, $physiotherapyProcedure),
            'urls' => [
                'index' => route('react.physiotherapy-procedures.index'),
                'myProcedures' => route('react.physiotherapy-procedures.my-procedures'),
                'show' => route('react.physiotherapy-procedures.show', $physiotherapyProcedure),
                'update' => route('react.physiotherapy-procedures.update', $physiotherapyProcedure),
                'destroy' => route('react.physiotherapy-procedures.destroy', $physiotherapyProcedure),
                'updateCounter' => route('react.physiotherapy-procedures.update-counter', $physiotherapyProcedure),
                'reviews' => route('react.physiotherapy-procedures.reviews.store', $physiotherapyProcedure),
                'appointment' => $physiotherapyProcedure->appointment_id
                    ? route('react.appointments.show', $physiotherapyProcedure->appointment_id)
                    : null,
            ],
        ]);
    }

    public function update(Request $request, PhysiotherapyProcedure $physiotherapyProcedure): RedirectResponse
    {
        $this->authorize('update', $physiotherapyProcedure);

        $appointment = Appointment::findOrFail($request->input('appointment_id', $physiotherapyProcedure->appointment_id));

        $validated = $request->validate([
            'physiotherapy_type_id' => 'required|exists:physiotherapy_types,id',
            'doctor_id' => [
                'required',
                Rule::exists('doctors', 'id')->where(fn ($q) => $q->where('branch_id', $appointment->branch_id)),
            ],
            'type' => 'required|string|max:255',
            'duration' => 'required|integer|min:1',
            'days_count' => 'required|integer|min:1',
            'description' => 'nullable|string',
            'notes' => 'nullable|string',
            'status' => 'required|in:pending,in_progress,completed,cancelled',
            'start_date' => 'required|string',
            'end_date' => 'nullable|string',
        ]);

        $physiotherapyProcedure->update([
            'physiotherapy_type_id' => $validated['physiotherapy_type_id'],
            'doctor_id' => $validated['doctor_id'],
            'type' => $validated['type'],
            'duration' => $validated['duration'],
            'days_count' => $validated['days_count'],
            'description' => $validated['description'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'status' => $validated['status'],
            'start_date' => Verta::parse($validated['start_date'])->datetime(),
            'end_date' => ! empty($validated['end_date'])
                ? Verta::parse($validated['end_date'])->datetime()
                : null,
        ]);

        return redirect()
            ->route('react.physiotherapy-procedures.show', $physiotherapyProcedure)
            ->with('success', localize('global.physiotherapy_procedure_updated_successfully'));
    }

    public function destroy(PhysiotherapyProcedure $physiotherapyProcedure): RedirectResponse
    {
        $this->authorize('delete', $physiotherapyProcedure);

        $physiotherapyProcedure->delete();

        return redirect()
            ->route('react.physiotherapy-procedures.index')
            ->with('success', localize('global.physiotherapy_procedure_deleted_successfully'));
    }

    public function updateCounter(Request $request, PhysiotherapyProcedure $physiotherapyProcedure): RedirectResponse
    {
        abort_unless($this->canUpdateProgress($request->user(), $physiotherapyProcedure), 403);

        $validated = $request->validate([
            'counter' => 'required|integer|min:0|max:'.$physiotherapyProcedure->days_count,
        ]);

        $counter = (int) $validated['counter'];
        $physiotherapyProcedure->update([
            'counter' => $counter,
            'status' => $counter >= $physiotherapyProcedure->days_count ? 'completed' : 'in_progress',
        ]);

        return redirect()
            ->back()
            ->with('success', localize('global.physiotherapy_procedure_counter_updated_successfully'));
    }

    public function storeReview(Request $request, PhysiotherapyProcedure $physiotherapyProcedure): RedirectResponse
    {
        $this->authorizeProcedureAccess($request->user(), $physiotherapyProcedure);
        abort_unless($this->showPermissions($request->user(), $physiotherapyProcedure)['addReview'], 403);

        $validated = $request->validate([
            'description' => 'required|string|max:1000',
            'status' => 'required|in:pending,in_progress,completed,cancelled',
            'days_count' => 'nullable|integer|min:0',
        ]);

        $physiotherapyProcedure->reviews()->create([
            'description' => $validated['description'],
            'status' => $validated['status'],
            'days_count' => $validated['days_count'] ?? 0,
        ]);

        return redirect()
            ->back()
            ->with('success', localize('global.review_created_successfully'));
    }

    public function updateReview(
        Request $request,
        PhysiotherapyProcedure $physiotherapyProcedure,
        PhysiotherapyProcedureReview $review,
    ): RedirectResponse {
        abort_unless((int) $review->physiotherapy_procedure_id === (int) $physiotherapyProcedure->id, 404);
        $this->authorize('update', $physiotherapyProcedure);

        $validated = $request->validate([
            'description' => 'required|string|max:1000',
            'status' => 'required|in:pending,in_progress,completed,cancelled',
            'days_count' => 'nullable|integer|min:0',
        ]);

        $review->update([
            'description' => $validated['description'],
            'status' => $validated['status'],
            'days_count' => $validated['days_count'] ?? 0,
        ]);

        return redirect()
            ->back()
            ->with('success', localize('global.review_updated_successfully'));
    }

    public function destroyReview(
        PhysiotherapyProcedure $physiotherapyProcedure,
        PhysiotherapyProcedureReview $review,
    ): RedirectResponse {
        abort_unless((int) $review->physiotherapy_procedure_id === (int) $physiotherapyProcedure->id, 404);
        $this->authorize('delete', $physiotherapyProcedure);

        $review->delete();

        return redirect()
            ->back()
            ->with('success', localize('global.review_deleted_successfully'));
    }

    private function renderList(Request $request, string $mode): Response
    {
        $filters = $this->listFilters($request);
        $query = $this->baseProcedureQuery();

        if ($mode === 'own') {
            $query->whereHas('doctor', fn ($doctorQuery) => $doctorQuery->where('user_id', $request->user()->id));
        }

        $filteredQuery = $this->applyProcedureFilters(clone $query, $filters);
        $procedures = $this->paginateProcedures($filteredQuery, $filters);
        $stats = $this->procedureStats($this->applyProcedureFilters(clone $query, $filters));

        $user = $request->user();
        $isOwn = $mode === 'own';

        return Inertia::render('PhysiotherapyProcedures/Index', [
            'mode' => $isOwn ? 'own' : 'all',
            'procedures' => $procedures,
            'stats' => $stats,
            'filters' => array_merge([
                'search' => '',
                'status' => '',
                'physiotherapy_type_id' => '',
                'doctor_id' => '',
                'start_date' => '',
                'end_date' => '',
                'sort_by' => 'created_at',
                'sort_order' => 'desc',
                'per_page' => '15',
            ], $filters),
            'filterOptions' => $this->filterOptions(),
            'permissions' => $this->listPermissions($user, $mode),
            'urls' => [
                'current' => $isOwn
                    ? route('react.physiotherapy-procedures.my-procedures')
                    : route('react.physiotherapy-procedures.index'),
                'index' => route('react.physiotherapy-procedures.index'),
                'myProcedures' => route('react.physiotherapy-procedures.my-procedures'),
                'reports' => route('react.physiotherapy-reports.index'),
                'show' => url('/react/physiotherapy-procedures'),
            ],
        ]);
    }
}
