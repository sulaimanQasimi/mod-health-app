<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Controllers\V1\Concerns\ManagesPacuListing;
use App\Http\Controllers\V1\Concerns\PaginatesInertiaIndex;
use App\Models\PACU;
use App\Models\Visit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class PACUController extends Controller
{
    use ManagesPacuListing;
    use PaginatesInertiaIndex;

    public function index(Request $request): Response
    {
        $this->authorizePacuMenu();

        $query = PACU::query()
            ->where('status', 'new')
            ->when($this->pacuBranchId(), fn ($q, $branchId) => $q->where('branch_id', $branchId))
            ->with(['patient:id,name,father_name,id_card'])
            ->orderByDesc('created_at');

        $this->applyPacuPatientFilters($query, $request);

        $paginator = $this->paginateQuery($query, $request);
        $items = $this->paginatedPacuItems($paginator);

        return Inertia::render('Pacus/Index', $this->listPagePayload($request, $items));
    }

    public function completed(Request $request): Response
    {
        $this->authorizePacuMenu();

        $query = PACU::query()
            ->where('status', 'completed')
            ->when($this->pacuBranchId(), fn ($q, $branchId) => $q->where('branch_id', $branchId))
            ->with(['patient:id,name,father_name,id_card'])
            ->orderByDesc('created_at');

        $this->applyPacuPatientFilters($query, $request);

        $paginator = $this->paginateQuery($query, $request);
        $items = $this->paginatedPacuItems($paginator);

        return Inertia::render('Pacus/Completed', $this->listPagePayload($request, $items));
    }

    public function report(Request $request): Response
    {
        $this->authorizePacuMenu();

        $items = [];
        if ($request->boolean('search')) {
            $items = $this->reportItems($request);
        }
        $summary = [
            'total' => count($items),
            'new' => count(array_filter($items, fn ($item) => $item['status'] === 'new')),
            'completed' => count(array_filter($items, fn ($item) => $item['status'] === 'completed')),
        ];

        return Inertia::render('Pacus/Report', [
            'items' => $items,
            'hasSearch' => $request->boolean('search'),
            'summary' => $summary,
            'analytics' => [
                'by_status' => [
                    ['name' => 'new', 'count' => $summary['new']],
                    ['name' => 'completed', 'count' => $summary['completed']],
                ],
                'by_department' => collect($items)
                    ->groupBy(fn ($item) => $item['department_name'] ?? '—')
                    ->map(fn ($items, $name) => ['name' => $name, 'count' => $items->count()])
                    ->sortByDesc('count')->values()->all(),
            ],
            'filters' => $this->collectFilters($request, [
                'patient_name',
                'status',
                'date_from',
                'date_to',
            ]),
            'urls' => [
                'current' => route('pacus.report'),
                'export' => route('pacus.export-report'),
                ...$this->pacuListUrls(),
            ],
        ]);
    }

    public function show(Request $request, PACU $pacu): Response
    {
        $this->authorizePacuMenu();

        $pacu->load([
            'patient:id,name,last_name,father_name,id_card,phone,nid,image,province_id,district_id,referred_by,created_at',
            'patient.province:id,name_dr',
            'patient.district:id,name_dr',
            'patient.recipient:id,name',
            'department:id,name',
            'branch:id,name',
            'visits' => fn ($q) => $q->with(['doctor:id,name,department_id', 'doctor.department:id,name']),
        ]);

        $user = $request->user();

        return Inertia::render('Pacus/Show', [
            'pacu' => $this->transformDetail($pacu),
            'permissions' => [
                'complete' => $pacu->status === 'new' && $user->can('show-pacu-menu'),
                'add_visit' => $pacu->status === 'new' && $user->can('show-pacu-menu'),
            ],
            'urls' => [
                'complete' => route('pacus.complete', $pacu),
                'store_visit' => route('pacus.visits.store', $pacu),
                'back' => $this->backUrlForPacuStatus($pacu->status),
                ...$this->pacuListUrls(),
            ],
        ]);
    }

    public function complete(PACU $pacu): RedirectResponse
    {
        $this->authorizePacuMenu();
        abort_unless($pacu->status === 'new', 403);

        $pacu->complete();

        return redirect()
            ->route('pacus.completed')
            ->with('success', localize('global.pacu_completed_successfully.'));
    }

    public function storeVisit(Request $request, PACU $pacu): RedirectResponse
    {
        $this->authorizePacuMenu();
        abort_unless($pacu->status === 'new', 403);

        $data = $request->validate([
            'description' => 'required|string',
        ]);

        Visit::create([
            'patient_id' => $pacu->patient_id,
            'p_a_c_u_id' => $pacu->id,
            'doctor_id' => $request->user()->id,
            'description' => $data['description'],
        ]);

        return redirect()
            ->back()
            ->with('success', localize('global.visit_created_successfully.'));
    }

    /**
     * @return array{data: array<int, mixed>, links: array<int, mixed>, meta: array<string, int|null>}
     */
    private function paginatedPacuItems(\Illuminate\Contracts\Pagination\LengthAwarePaginator $paginator): array
    {
        $from = $paginator->firstItem();

        return [
            'data' => collect($paginator->items())
                ->map(function (PACU $pacu, int $index) use ($from) {
                    return $this->transformPacuListItem($pacu, $from ? $from + $index : null);
                })
                ->values()
                ->all(),
            'links' => $paginator->linkCollection()->toArray(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem(),
            ],
        ];
    }

    /**
     * @param  array{data: array<int, mixed>, links: array<int, mixed>, meta: array<string, int|null>}  $items
     * @return array<string, mixed>
     */
    private function listPagePayload(Request $request, array $items): array
    {
        return [
            'pacus' => $items,
            'filters' => $this->collectFilters($request, $this->pacuListFilterKeys()),
            'urls' => [
                'current' => $request->url(),
                ...$this->pacuListUrls(),
            ],
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function reportItems(Request $request): array
    {
        $query = DB::table('p_a_c_u_s as pa')
            ->leftJoin('patients as p', 'pa.patient_id', '=', 'p.id')
            ->leftJoin('branches as b', 'pa.branch_id', '=', 'b.id')
            ->leftJoin('departments as dep', 'pa.department_id', '=', 'dep.id')
            ->select(
                'pa.id',
                'p.name as patient_name',
                'b.name as branch_name',
                'pa.status',
                'pa.created_at',
                'dep.name as department_name',
            )
            ->when($this->pacuBranchId(), fn ($q, $branchId) => $q->where('pa.branch_id', $branchId));

        if ($request->filled('patient_name')) {
            $query->where('p.name', 'like', '%'.$request->patient_name.'%');
        }

        if ($request->filled('status')) {
            $query->where('pa.status', $request->status);
        }

        $this->applyPacuReportDateRange($query, $request);

        return $query
            ->orderByDesc('pa.created_at')
            ->get()
            ->map(fn ($row) => [
                'id' => $row->id,
                'patient_name' => $row->patient_name,
                'branch_name' => $row->branch_name,
                'status' => $row->status,
                'created_at' => $this->formatPacuDate($row->created_at),
                'department_name' => $row->department_name,
            ])
            ->all();
    }

    /**
     * @param  \Illuminate\Database\Query\Builder  $query
     */
    private function applyPacuReportDateRange($query, Request $request): void
    {
        if ($request->filled('date_from')) {
            try {
                $query->whereDate('pa.created_at', '>=', \Hekmatinasser\Verta\Verta::parse($request->date_from)->datetime());
            } catch (\Throwable) {
            }
        }

        if ($request->filled('date_to')) {
            try {
                $query->whereDate('pa.created_at', '<=', \Hekmatinasser\Verta\Verta::parse($request->date_to)->datetime());
            } catch (\Throwable) {
            }
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function transformDetail(PACU $pacu): array
    {
        return [
            'id' => $pacu->id,
            'description' => $pacu->description,
            'status' => $pacu->status,
            'created_at' => $this->formatPacuDate($pacu->created_at),
            'appointment_id' => $pacu->appointment_id,
            'department_name' => $pacu->department?->name,
            'branch_name' => $pacu->branch?->name,
            'patient' => $pacu->patient ? [
                'id' => $pacu->patient->id,
                'name' => $pacu->patient->name,
                'last_name' => $pacu->patient->last_name,
                'father_name' => $pacu->patient->father_name,
                'id_card' => $pacu->patient->id_card,
                'phone' => $pacu->patient->phone,
                'nid' => $pacu->patient->nid,
                'province_name' => $pacu->patient->province?->name_dr,
                'district_name' => $pacu->patient->district?->name_dr,
                'recipient_name' => $pacu->patient->recipient?->name,
                'patient_created_at' => $this->formatPacuDate($pacu->patient->created_at),
                'image' => $pacu->patient->image,
            ] : null,
            'visits' => $pacu->visits->map(fn (Visit $visit) => [
                'id' => $visit->id,
                'description' => $visit->description,
                'department_name' => $visit->doctor?->department?->name,
                'doctor_name' => $visit->doctor?->name,
            ])->values()->all(),
        ];
    }
}
