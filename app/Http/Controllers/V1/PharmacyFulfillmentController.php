<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Controllers\V1\Concerns\AuthorizesPharmacyStockAccess;
use App\Http\Controllers\V1\Concerns\PaginatesInertiaIndex;
use App\Models\Medicine;
use App\Models\Pharmacy;
use App\Models\PharmacyFulfillment;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class PharmacyFulfillmentController extends Controller
{
    use AuthorizesPharmacyStockAccess;
    use PaginatesInertiaIndex;

    private const INDEX_FILTER_KEYS = [
        'search', 'medicine_id', 'unit_type', 'form_no', 'amount_from', 'amount_to',
        'pharmacy_id', 'date_from', 'date_to', 'sort_by', 'sort_order', 'per_page',
    ];

    private const STOCK_FILTER_KEYS = [
        'search', 'medicine_id', 'pharmacy_id', 'stock_status', 'sort_by', 'sort_order', 'per_page',
    ];

    public function index(Request $request): Response
    {
        $user = $request->user();
        $this->authorizePharmacyFulfillment($user);

        $paginator = $this->paginateQuery(
            $this->buildIndexQuery($request, $user),
            $request,
            15,
        );

        return Inertia::render('PharmacyFulfillments/Index', [
            'fulfillments' => $this->paginationPayload($paginator, fn (PharmacyFulfillment $item) => [
                'id' => $item->id,
                'medicine_name' => $item->medicine?->name,
                'unit_type' => $item->unit_type,
                'amount' => $item->amount,
                'form_no' => $item->form_no,
                'date' => $item->date?->format('Y-m-d'),
                'pharmacy_name' => $item->pharmacy?->name,
                'user_name' => $item->user ? trim("{$item->user->name} {$item->user->last_name}") : null,
                'created_by_name' => $item->createdBy ? trim("{$item->createdBy->name} {$item->createdBy->last_name}") : null,
                'created_at' => $item->created_at?->format('Y-m-d H:i'),
            ]),
            'filters' => $this->collectFilters($request, self::INDEX_FILTER_KEYS),
            'filterOptions' => $this->indexFilterOptions($user),
            'userPharmacies' => $user->activePharmacies->map(fn (Pharmacy $pharmacy) => [
                'id' => $pharmacy->id,
                'name' => $pharmacy->name,
            ])->values()->all(),
            'permissions' => $this->fulfillmentPermissions($user),
            'urls' => $this->indexUrls(),
        ]);
    }

    public function stock(Request $request): Response
    {
        $user = $request->user();
        $this->authorizePharmacyFulfillment($user);

        $allowedPharmacyIds = $this->allowedPharmacyIds(
            $user,
            $request->filled('pharmacy_id') ? (int) $request->pharmacy_id : null,
        );

        $stockStats = [
            'total_items' => 0,
            'total_stock' => 0,
            'total_income' => 0,
            'total_outcome' => 0,
            'total_low_stock' => 0,
            'total_out_of_stock' => 0,
        ];

        if (empty($allowedPharmacyIds)) {
            return Inertia::render('PharmacyFulfillments/Stock', $this->stockPagePayload(
                $request,
                $user,
                new \Illuminate\Pagination\LengthAwarePaginator([], 0, 15),
                $stockStats,
            ));
        }

        $usageSql = "(SELECT p.pharmacy_id, COALESCE(pai.medicine_id, pi.medicine_id) as medicine_id, COUNT(*) as total
            FROM prescription_items pi
            LEFT JOIN prescription_alternative_items pai ON pai.prescription_item_id = pi.id AND pai.prescription_id = pi.prescription_id AND pai.is_selected = 1 AND pai.deleted_at IS NULL
            JOIN prescriptions p ON pi.prescription_id = p.id
            WHERE pi.deleted_at IS NULL AND p.deleted_at IS NULL AND p.pharmacy_id IS NOT NULL
            GROUP BY p.pharmacy_id, COALESCE(pai.medicine_id, pi.medicine_id))";

        $unionSql = "((SELECT pharmacy_id, medicine_id FROM pharmacy_fulfillments WHERE deleted_at IS NULL)
            UNION
            (SELECT pharmacy_id, medicine_id FROM outcomes WHERE deleted_at IS NULL AND pharmacy_id IS NOT NULL)
            UNION
            (SELECT p.pharmacy_id as pharmacy_id, COALESCE(pai.medicine_id, pi.medicine_id) as medicine_id
             FROM prescription_items pi
             LEFT JOIN prescription_alternative_items pai ON pai.prescription_item_id = pi.id AND pai.prescription_id = pi.prescription_id AND pai.is_selected = 1 AND pai.deleted_at IS NULL
             JOIN prescriptions p ON pi.prescription_id = p.id
             WHERE pi.deleted_at IS NULL AND p.deleted_at IS NULL AND p.pharmacy_id IS NOT NULL)) as u";
        $ftSql = '(SELECT pharmacy_id, medicine_id, SUM(CAST(amount AS UNSIGNED)) as total FROM pharmacy_fulfillments WHERE deleted_at IS NULL GROUP BY pharmacy_id, medicine_id)';
        $stockSql = 'COALESCE(ft.total, 0) - COALESCE(ot.total, 0)';

        $baseQuery = DB::table(DB::raw($unionSql))
            ->join('medicines as m', 'm.id', '=', 'u.medicine_id')
            ->join('pharmacies as p', 'p.id', '=', 'u.pharmacy_id')
            ->leftJoin(DB::raw("{$ftSql} as ft"), function ($join) {
                $join->on('ft.pharmacy_id', '=', 'u.pharmacy_id')->on('ft.medicine_id', '=', 'u.medicine_id');
            })
            ->leftJoin(DB::raw("{$usageSql} as ot"), function ($join) {
                $join->on('ot.pharmacy_id', '=', 'u.pharmacy_id')->on('ot.medicine_id', '=', 'u.medicine_id');
            })
            ->whereIn('u.pharmacy_id', $allowedPharmacyIds)
            ->whereNull('m.deleted_at')
            ->whereNull('p.deleted_at');

        if ($request->filled('search')) {
            $search = $request->search;
            $baseQuery->where(function ($query) use ($search) {
                $query->where('m.name', 'like', "%{$search}%")
                    ->orWhere('p.name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('medicine_id')) {
            $baseQuery->where('m.id', (int) $request->medicine_id);
        }

        if ($request->stock_status === 'out_of_stock') {
            $baseQuery->whereRaw("{$stockSql} <= 0");
        } elseif ($request->stock_status === 'low_stock') {
            $baseQuery->whereRaw("{$stockSql} > 0 AND {$stockSql} <= 10");
        }

        $stockStats['total_income'] = (int) DB::table('pharmacy_fulfillments')
            ->whereNull('deleted_at')
            ->whereIn('pharmacy_id', $allowedPharmacyIds)
            ->sum(DB::raw('CAST(amount AS UNSIGNED)'));
        $stockStats['total_outcome'] = (int) DB::table('prescription_items as pi')
            ->leftJoin('prescription_alternative_items as pai', function ($join) {
                $join->on('pai.prescription_item_id', '=', 'pi.id')
                    ->on('pai.prescription_id', '=', 'pi.prescription_id')
                    ->whereRaw('(pai.is_selected = 1 AND pai.deleted_at IS NULL)');
            })
            ->join('prescriptions as p', 'pi.prescription_id', '=', 'p.id')
            ->whereNull('pi.deleted_at')
            ->whereNull('p.deleted_at')
            ->whereIn('p.pharmacy_id', $allowedPharmacyIds)
            ->count();

        $statsResult = (clone $baseQuery)->selectRaw("
            COUNT(*) as total_items,
            COALESCE(SUM({$stockSql}), 0) as total_stock,
            SUM(CASE WHEN {$stockSql} > 0 AND {$stockSql} <= 10 THEN 1 ELSE 0 END) as total_low_stock,
            SUM(CASE WHEN {$stockSql} <= 0 THEN 1 ELSE 0 END) as total_out_of_stock
        ")->first();

        if ($statsResult) {
            $stockStats['total_items'] = (int) $statsResult->total_items;
            $stockStats['total_stock'] = (int) $statsResult->total_stock;
            $stockStats['total_low_stock'] = (int) $statsResult->total_low_stock;
            $stockStats['total_out_of_stock'] = (int) $statsResult->total_out_of_stock;
        }

        $query = (clone $baseQuery)->select(
            'm.id as medicine_id',
            'm.name as medicine_name',
            'p.id as pharmacy_id',
            'p.name as pharmacy_name',
            DB::raw('COALESCE(ft.total, 0) as income'),
            DB::raw('COALESCE(ot.total, 0) as outcome'),
            DB::raw("{$stockSql} as stock"),
        );

        $sortBy = $request->get('sort_by', 'medicine');
        $sortOrder = strtolower($request->get('sort_order', 'asc')) === 'desc' ? 'desc' : 'asc';
        $sortableColumns = [
            'medicine' => 'm.name',
            'pharmacy' => 'p.name',
            'income' => 'income',
            'outcome' => 'outcome',
            'stock' => 'stock',
        ];
        $sortColumn = $sortableColumns[$sortBy] ?? 'm.name';
        $query->orderBy($sortColumn, $sortOrder)->orderBy('m.name')->orderBy('p.name');

        $perPage = (int) $request->get('per_page', 15);
        $paginator = $query->paginate($perPage > 0 ? $perPage : 15)->withQueryString();

        return Inertia::render('PharmacyFulfillments/Stock', $this->stockPagePayload(
            $request,
            $user,
            $paginator,
            $stockStats,
        ));
    }

    public function create(Request $request): Response|RedirectResponse
    {
        $this->authorizePharmacyFulfillment($request->user());

        $userPharmacy = $request->user()->activePharmacies()->first();
        if (! $userPharmacy && ! $this->isPharmacyAdmin($request->user())) {
            return redirect()
                ->route('react.pharmacy-fulfillments.index')
                ->with('warning', localize('global.no_pharmacy_access'));
        }

        return Inertia::render('PharmacyFulfillments/Create', [
            'formData' => $this->buildFormData($request->user()),
            'userPharmacy' => $userPharmacy ? ['id' => $userPharmacy->id, 'name' => $userPharmacy->name] : null,
            'urls' => $this->formUrls(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizePharmacyFulfillment($request->user());

        $user = $request->user();
        $userPharmacy = $user->activePharmacies()->first();

        if (! $userPharmacy && ! $this->isPharmacyAdmin($user)) {
            return redirect()
                ->route('react.pharmacy-fulfillments.index')
                ->with('warning', localize('global.no_pharmacy_access'));
        }

        $data = $this->validateFulfillment($request);
        $data['pharmacy_id'] = $userPharmacy?->id ?? $data['pharmacy_id'] ?? null;
        $data['user_id'] = $user->id;

        if ($request->hasFile('form')) {
            $file = $request->file('form');
            $filename = time().'_'.uniqid().'_'.$file->getClientOriginalName();
            $data['form'] = $file->storeAs('pharmacy_fulfillments', $filename, 'public');
        }

        if (! empty($data['date'])) {
            $data['date'] = $this->parseOptionalDate($data['date']);
        }

        PharmacyFulfillment::create($data);

        return redirect()
            ->route('react.pharmacy-fulfillments.index')
            ->with('success', localize('global.pharmacy_fulfillment_created_successfully'));
    }

    public function show(Request $request, PharmacyFulfillment $pharmacyFulfillment): Response
    {
        $this->authorizeFulfillmentRecord($request->user(), $pharmacyFulfillment);
        $pharmacyFulfillment->load(['medicine', 'pharmacy', 'user', 'createdBy', 'updatedBy']);

        return Inertia::render('PharmacyFulfillments/Show', [
            'fulfillment' => $this->transformFulfillment($pharmacyFulfillment),
            'permissions' => $this->fulfillmentPermissions($request->user()),
            'urls' => $this->recordUrls($pharmacyFulfillment),
        ]);
    }

    public function edit(Request $request, PharmacyFulfillment $pharmacyFulfillment): Response
    {
        $this->authorizeFulfillmentRecord($request->user(), $pharmacyFulfillment);
        $pharmacyFulfillment->load(['medicine', 'pharmacy']);

        return Inertia::render('PharmacyFulfillments/Edit', [
            'fulfillment' => [
                'id' => $pharmacyFulfillment->id,
                'medicine_id' => (string) $pharmacyFulfillment->medicine_id,
                'unit_type' => $pharmacyFulfillment->unit_type ?? '',
                'amount' => $pharmacyFulfillment->amount,
                'form_no' => $pharmacyFulfillment->form_no,
                'date' => $pharmacyFulfillment->date?->format('Y-m-d'),
                'form_path' => $pharmacyFulfillment->form,
                'pharmacy_name' => $pharmacyFulfillment->pharmacy?->name,
            ],
            'formData' => $this->buildFormData($request->user()),
            'urls' => $this->formUrls($pharmacyFulfillment),
        ]);
    }

    public function update(Request $request, PharmacyFulfillment $pharmacyFulfillment): RedirectResponse
    {
        $this->authorizeFulfillmentRecord($request->user(), $pharmacyFulfillment);

        $data = $this->validateFulfillment($request, false);

        if (! empty($data['date'])) {
            $data['date'] = $this->parseOptionalDate($data['date']);
        }

        if ($request->hasFile('form')) {
            if ($pharmacyFulfillment->form && Storage::disk('public')->exists($pharmacyFulfillment->form)) {
                Storage::disk('public')->delete($pharmacyFulfillment->form);
            }

            $file = $request->file('form');
            $filename = time().'_'.uniqid().'_'.$file->getClientOriginalName();
            $data['form'] = $file->storeAs('pharmacy_fulfillments', $filename, 'public');
        }

        $pharmacyFulfillment->update($data);

        return redirect()
            ->route('react.pharmacy-fulfillments.index')
            ->with('success', localize('global.pharmacy_fulfillment_updated_successfully'));
    }

    public function destroy(Request $request, PharmacyFulfillment $pharmacyFulfillment): RedirectResponse
    {
        $this->authorizeFulfillmentRecord($request->user(), $pharmacyFulfillment);
        $pharmacyFulfillment->delete();

        return redirect()
            ->route('react.pharmacy-fulfillments.index')
            ->with('success', localize('global.pharmacy_fulfillment_deleted_successfully'));
    }

    /**
     * @return Builder<PharmacyFulfillment>
     */
    private function buildIndexQuery(Request $request, \App\Models\User $user): Builder
    {
        $query = PharmacyFulfillment::query()->with(['medicine', 'pharmacy', 'user', 'createdBy']);
        $userPharmacies = $user->activePharmacies;

        if ($userPharmacies->isNotEmpty() && ! $this->isPharmacyAdmin($user)) {
            $query->whereIn('pharmacy_id', $userPharmacies->pluck('id'));
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($searchQuery) use ($search) {
                $searchQuery->whereHas('medicine', fn ($medicineQuery) => $medicineQuery->where('name', 'like', "%{$search}%"))
                    ->orWhere('form_no', 'like', "%{$search}%")
                    ->orWhere('unit_type', 'like', "%{$search}%");
            });
        }

        if ($request->filled('medicine_id')) {
            $query->where('medicine_id', $request->medicine_id);
        }

        if ($request->filled('unit_type')) {
            $query->where('unit_type', 'like', '%'.$request->unit_type.'%');
        }

        if ($request->filled('form_no')) {
            $query->where('form_no', 'like', '%'.$request->form_no.'%');
        }

        if ($request->filled('amount_from')) {
            $query->where('amount', '>=', $request->amount_from);
        }

        if ($request->filled('amount_to')) {
            $query->where('amount', '<=', $request->amount_to);
        }

        if ($request->filled('pharmacy_id') && $this->isPharmacyAdmin($user)) {
            $query->where('pharmacy_id', $request->pharmacy_id);
        }

        if ($from = $this->parseOptionalDate($request->date_from)) {
            $query->whereDate('date', '>=', $from);
        }

        if ($to = $this->parseOptionalDate($request->date_to)) {
            $query->whereDate('date', '<=', $to);
        }

        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc') === 'asc' ? 'asc' : 'desc';
        $query->orderBy($sortBy, $sortOrder);

        return $query;
    }

    /**
     * @return array<string, mixed>
     */
    private function stockPagePayload(
        Request $request,
        \App\Models\User $user,
        \Illuminate\Contracts\Pagination\LengthAwarePaginator $paginator,
        array $stockStats,
    ): array {
        return [
            'stockItems' => [
                'data' => collect($paginator->items())->map(fn ($item) => [
                    'medicine_id' => $item->medicine_id,
                    'medicine_name' => $item->medicine_name,
                    'pharmacy_id' => $item->pharmacy_id,
                    'pharmacy_name' => $item->pharmacy_name,
                    'income' => (int) $item->income,
                    'outcome' => (int) $item->outcome,
                    'stock' => (int) $item->stock,
                ])->values()->all(),
                'links' => method_exists($paginator, 'linkCollection') ? $paginator->linkCollection()->toArray() : [],
                'meta' => [
                    'current_page' => $paginator->currentPage(),
                    'last_page' => $paginator->lastPage(),
                    'per_page' => $paginator->perPage(),
                    'total' => $paginator->total(),
                    'from' => $paginator->firstItem(),
                    'to' => $paginator->lastItem(),
                ],
            ],
            'stockStats' => $stockStats,
            'filters' => $this->collectFilters($request, self::STOCK_FILTER_KEYS),
            'filterOptions' => [
                'pharmacies' => $this->isPharmacyAdmin($user)
                    ? Pharmacy::query()->orderBy('name')->get(['id', 'name'])
                    : $user->activePharmacies->map(fn (Pharmacy $pharmacy) => ['id' => $pharmacy->id, 'name' => $pharmacy->name])->values()->all(),
                'medicines' => Medicine::query()->whereNull('deleted_at')->orderBy('name')->get(['id', 'name']),
                'stockStatuses' => ['out_of_stock', 'low_stock'],
            ],
            'userPharmacies' => $user->activePharmacies->map(fn (Pharmacy $pharmacy) => [
                'id' => $pharmacy->id,
                'name' => $pharmacy->name,
            ])->values()->all(),
            'urls' => [
                'index' => route('react.pharmacy-fulfillments.stock'),
                'fulfillments' => route('react.pharmacy-fulfillments.index'),
                'outcomes' => route('react.outcomes.index'),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function indexFilterOptions(\App\Models\User $user): array
    {
        return [
            'pharmacies' => $this->isPharmacyAdmin($user)
                ? Pharmacy::query()->orderBy('name')->get(['id', 'name'])
                : [],
            'medicines' => Medicine::query()->whereNull('deleted_at')->orderBy('name')->get(['id', 'name']),
        ];
    }

    /**
     * @return array<string, string>
     */
    private function indexUrls(): array
    {
        return [
            'index' => route('react.pharmacy-fulfillments.index'),
            'create' => route('react.pharmacy-fulfillments.create'),
            'show' => url('/react/pharmacy-fulfillments'),
            'edit' => url('/react/pharmacy-fulfillments'),
            'destroy' => url('/react/pharmacy-fulfillments'),
            'stock' => route('react.pharmacy-fulfillments.stock'),
        ];
    }

    /**
     * @return array<string, string>
     */
    private function formUrls(?PharmacyFulfillment $fulfillment = null): array
    {
        return [
            'index' => route('react.pharmacy-fulfillments.index'),
            'store' => route('react.pharmacy-fulfillments.store'),
            'update' => $fulfillment ? route('react.pharmacy-fulfillments.update', $fulfillment) : '',
            'back' => route('react.pharmacy-fulfillments.index'),
        ];
    }

    /**
     * @return array<string, string>
     */
    private function recordUrls(PharmacyFulfillment $fulfillment): array
    {
        return [
            'index' => route('react.pharmacy-fulfillments.index'),
            'edit' => route('react.pharmacy-fulfillments.edit', $fulfillment),
            'destroy' => route('react.pharmacy-fulfillments.destroy', $fulfillment),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function transformFulfillment(PharmacyFulfillment $fulfillment): array
    {
        return [
            'id' => $fulfillment->id,
            'medicine_name' => $fulfillment->medicine?->name,
            'unit_type' => $fulfillment->unit_type,
            'amount' => $fulfillment->amount,
            'form_no' => $fulfillment->form_no,
            'date' => $fulfillment->date?->format('Y-m-d'),
            'form_url' => $fulfillment->form ? Storage::disk('public')->url($fulfillment->form) : null,
            'pharmacy_name' => $fulfillment->pharmacy?->name,
            'user_name' => $fulfillment->user ? trim("{$fulfillment->user->name} {$fulfillment->user->last_name}") : null,
            'created_by_name' => $fulfillment->createdBy ? trim("{$fulfillment->createdBy->name} {$fulfillment->createdBy->last_name}") : null,
            'updated_by_name' => $fulfillment->updatedBy ? trim("{$fulfillment->updatedBy->name} {$fulfillment->updatedBy->last_name}") : null,
            'created_at' => $fulfillment->created_at?->format('Y-m-d H:i'),
            'updated_at' => $fulfillment->updated_at?->format('Y-m-d H:i'),
        ];
    }

    /**
     * @return array{medicines: list<array{id: int, name: string}>}
     */
    private function buildFormData(\App\Models\User $user): array
    {
        return [
            'medicines' => Medicine::query()->whereNull('deleted_at')->orderBy('name')->get(['id', 'name'])
                ->map(fn (Medicine $medicine) => ['id' => $medicine->id, 'name' => $medicine->name])
                ->values()
                ->all(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function validateFulfillment(Request $request, bool $requireAll = true): array
    {
        return $request->validate([
            'medicine_id' => ($requireAll ? 'required' : 'sometimes').'|exists:medicines,id',
            'unit_type' => 'nullable|string|max:255',
            'amount' => ($requireAll ? 'required' : 'sometimes').'|string|max:191',
            'form_no' => ($requireAll ? 'required' : 'sometimes').'|string|max:191',
            'date' => ($requireAll ? 'required' : 'sometimes').'|string',
            'form' => 'nullable|file|mimes:pdf|max:10240',
        ]);
    }
}
