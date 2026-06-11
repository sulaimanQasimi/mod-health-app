<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Controllers\V1\Concerns\ManagesBloodBankListing;
use App\Http\Controllers\V1\Concerns\PaginatesInertiaIndex;
use App\Models\BloodUnit;
use App\Services\BloodBankStockService;
use App\Services\BloodUnitReceiveService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BloodUnitController extends Controller
{
    use ManagesBloodBankListing;
    use PaginatesInertiaIndex;

    public function index(Request $request): Response
    {
        $this->authorizeBloodBankMenu();

        $branchId = $this->bloodBankBranchId();
        $expiredArchivedCount = app(BloodBankStockService::class)->archiveExpiredUnits($branchId, $request->user()->id);

        $query = BloodUnit::query()
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId));

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('blood_group')) {
            $query->where('blood_group', $request->blood_group);
        }

        if ($request->filled('rh')) {
            $query->where('rh', $request->rh);
        }

        if ($request->filled('component_type')) {
            $query->where('component_type', $request->component_type);
        }

        if ($request->filled('q')) {
            $query->where('bag_number', 'like', '%'.trim($request->q).'%');
        }

        if ($request->filled('expires_within')) {
            $days = max(1, min(90, (int) $request->expires_within));
            $query->where('status', 'available')
                ->where('expires_at', '>', now())
                ->where('expires_at', '<=', now()->addDays($days));
        }

        $sort = $request->input('sort', 'created_at');
        if ($sort === 'expires_at') {
            $query->orderBy('expires_at');
        } else {
            $query->orderByDesc('created_at');
        }

        $paginator = $this->paginateQuery($query, $request, 30);
        $from = $paginator->firstItem();

        return Inertia::render('BloodBanks/Inventory', [
            'units' => [
                'data' => collect($paginator->items())->map(function (BloodUnit $unit, int $index) use ($from) {
                    return [
                        'id' => $unit->id,
                        'row_number' => $from ? $from + $index : null,
                        'bag_number' => $unit->bag_number,
                        'blood_group' => $unit->blood_group,
                        'rh' => $unit->rh,
                        'component_type' => $unit->component_type,
                        'status' => $unit->status,
                        'expires_at' => $unit->expires_at ? verta($unit->expires_at)->format('Y-m-d') : null,
                        'created_at' => $unit->created_at ? verta($unit->created_at)->format('Y-m-d') : null,
                        'urls' => [
                            'show' => route('blood_banks.inventory.show', $unit),
                        ],
                    ];
                })->values()->all(),
                'links' => $paginator->linkCollection()->toArray(),
                'meta' => [
                    'current_page' => $paginator->currentPage(),
                    'last_page' => $paginator->lastPage(),
                    'per_page' => $paginator->perPage(),
                    'total' => $paginator->total(),
                    'from' => $paginator->firstItem(),
                    'to' => $paginator->lastItem(),
                ],
            ],
            'expiredArchivedCount' => $expiredArchivedCount,
            'filters' => $this->collectFilters($request, [
                'status',
                'blood_group',
                'rh',
                'component_type',
                'q',
                'expires_within',
                'sort',
                'per_page',
            ]),
            'filterOptions' => [
                'bloodGroups' => ['A', 'B', 'AB', 'O'],
                'bloodComponentTypes' => $this->bloodComponentTypes(),
                'statuses' => ['available', 'reserved', 'issued', 'quarantine', 'discarded', 'expired'],
            ],
            'permissions' => [
                'canCreate' => $request->user()->can('receive-blood-units')
                    || $request->user()->can('manage-blood-inventory'),
            ],
            'urls' => [
                'current' => route('react.blood-banks.inventory'),
                'create' => route('react.blood-banks.inventory.create'),
                ...$this->bloodBankListUrls(),
            ],
        ]);
    }

    public function create(Request $request): Response
    {
        $this->authorizeBloodBankMenu();
        $this->authorizeReceiveBloodUnits();

        return Inertia::render('BloodBanks/InventoryCreate', [
            'departments' => $this->bloodRequestFilterOptions()['departments'],
            'filterOptions' => [
                'bloodGroups' => ['A', 'B', 'AB', 'O'],
                'bloodComponentTypes' => $this->bloodComponentTypes(),
            ],
            'urls' => [
                'back' => route('react.blood-banks.inventory'),
                'store' => route('react.blood-banks.inventory.store'),
                ...$this->bloodBankListUrls(),
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeBloodBankMenu();
        $this->authorizeReceiveBloodUnits();

        app(BloodUnitReceiveService::class)->receive($request);

        return redirect()
            ->route('react.blood-banks.inventory')
            ->with('success', localize('global.blood_unit_received_success'));
    }

    protected function authorizeReceiveBloodUnits(): void
    {
        $user = request()->user();
        abort_unless(
            $user?->can('receive-blood-units') || $user?->can('manage-blood-inventory'),
            403,
        );
    }
}
