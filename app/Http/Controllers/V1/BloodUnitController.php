<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Controllers\V1\Concerns\ManagesBloodBankListing;
use App\Http\Controllers\V1\Concerns\PaginatesInertiaIndex;
use App\Models\BloodUnit;
use App\Models\BloodUnitTest;
use App\Services\BloodBankStockService;
use App\Services\BloodUnitManagementService;
use App\Services\BloodUnitReceiveService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
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
                            'show' => route('react.blood-banks.inventory.show', $unit),
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

    public function show(Request $request, BloodUnit $bloodUnit): Response
    {
        $this->authorizeBloodBankMenu();
        $this->ensureBranchBloodUnit($bloodUnit);

        $bloodUnit->load([
            'branch:id,name',
            'stockMovements.user:id,name',
            'test.testedBy:id,name',
            'tests.testedBy:id,name',
            'donation.donor.department:id,name',
            'donation.donor.patient:id,name,last_name',
            'donation.samples',
        ]);

        $user = $request->user();

        return Inertia::render('BloodBanks/InventoryShow', [
            'unit' => $this->transformBloodUnitShow($bloodUnit),
            'filterOptions' => [
                'bloodGroups' => ['A', 'B', 'AB', 'O'],
                'testResults' => BloodUnitTest::RESULT_VALUES,
            ],
            'permissions' => [
                'manage' => $user->can('receive-blood-units') || $user->can('manage-blood-inventory'),
                'canQuarantine' => in_array($bloodUnit->status, ['available', 'quarantine'], true),
                'canDiscard' => in_array($bloodUnit->status, ['available', 'quarantine'], true),
                'canReleaseAfterTests' => ($bloodUnit->test?->overall_status ?? 'pending') === 'passed'
                    && $bloodUnit->status === 'quarantine',
            ],
            'flash' => [
                'success' => session('success'),
                'error' => session('error'),
            ],
            'urls' => [
                'back' => route('react.blood-banks.inventory'),
                'saveTests' => route('react.blood-banks.inventory.tests.save', $bloodUnit),
                'approveAfterTests' => route('react.blood-banks.inventory.tests.approve', $bloodUnit),
                'quarantine' => route('react.blood-banks.inventory.quarantine', $bloodUnit),
                'releaseQuarantine' => route('react.blood-banks.inventory.release-quarantine', $bloodUnit),
                'discard' => route('react.blood-banks.inventory.discard', $bloodUnit),
                ...$this->bloodBankListUrls(),
            ],
        ]);
    }

    public function saveTests(Request $request, BloodUnit $bloodUnit): RedirectResponse
    {
        $this->authorizeBloodBankMenu();
        $this->ensureBranchBloodUnit($bloodUnit);
        $this->authorizeReceiveBloodUnits();

        $validated = $request->validate([
            'abo_result' => ['nullable', 'string', Rule::in(['A', 'B', 'AB', 'O'])],
            'rh_result' => ['nullable', 'string', Rule::in(['+', '-'])],
            'dct_result' => ['required', Rule::in(BloodUnitTest::RESULT_VALUES)],
            'ict_result' => ['required', Rule::in(BloodUnitTest::RESULT_VALUES)],
            'hbs_result' => ['required', Rule::in(BloodUnitTest::RESULT_VALUES)],
            'hcv_result' => ['required', Rule::in(BloodUnitTest::RESULT_VALUES)],
            'hiv_result' => ['required', Rule::in(BloodUnitTest::RESULT_VALUES)],
            'vdrl_result' => ['required', Rule::in(BloodUnitTest::RESULT_VALUES)],
            'remarks' => ['nullable', 'string', 'max:5000'],
        ]);

        app(BloodUnitManagementService::class)->saveTests($bloodUnit, $validated, (int) $request->user()->id);

        return redirect()
            ->route('react.blood-banks.inventory.show', $bloodUnit)
            ->with('success', localize('global.blood_unit_tests_saved'));
    }

    public function approveAfterTests(BloodUnit $bloodUnit): RedirectResponse
    {
        $this->authorizeBloodBankMenu();
        $this->ensureBranchBloodUnit($bloodUnit);
        $this->authorizeReceiveBloodUnits();

        try {
            app(BloodUnitManagementService::class)->approveAfterTests($bloodUnit);
        } catch (ValidationException $e) {
            return redirect()
                ->route('react.blood-banks.inventory.show', $bloodUnit)
                ->with('error', collect($e->errors())->flatten()->first());
        } catch (\Throwable $e) {
            return redirect()
                ->route('react.blood-banks.inventory.show', $bloodUnit)
                ->with('error', $e->getMessage());
        }

        return redirect()
            ->route('react.blood-banks.inventory.show', $bloodUnit)
            ->with('success', localize('global.blood_unit_released_after_tests'));
    }

    public function discard(Request $request, BloodUnit $bloodUnit): RedirectResponse
    {
        $this->authorizeBloodBankMenu();
        $this->ensureBranchBloodUnit($bloodUnit);
        $this->authorizeReceiveBloodUnits();

        $request->validate(['reason' => 'nullable|string|max:2000']);

        try {
            app(BloodUnitManagementService::class)->discard($bloodUnit, $request->input('reason'));
        } catch (\Throwable $e) {
            return redirect()
                ->route('react.blood-banks.inventory.show', $bloodUnit)
                ->with('error', $e->getMessage());
        }

        return redirect()
            ->route('react.blood-banks.inventory')
            ->with('success', localize('global.blood_unit_discarded_success'));
    }

    public function quarantine(Request $request, BloodUnit $bloodUnit): RedirectResponse
    {
        $this->authorizeBloodBankMenu();
        $this->ensureBranchBloodUnit($bloodUnit);
        $this->authorizeReceiveBloodUnits();

        $request->validate(['reason' => 'nullable|string|max:2000']);

        try {
            app(BloodUnitManagementService::class)->setQuarantine($bloodUnit, true, $request->input('reason'));
        } catch (\Throwable $e) {
            return redirect()
                ->route('react.blood-banks.inventory.show', $bloodUnit)
                ->with('error', $e->getMessage());
        }

        return redirect()
            ->route('react.blood-banks.inventory.show', $bloodUnit)
            ->with('success', localize('global.blood_unit_quarantine_set'));
    }

    public function releaseQuarantine(Request $request, BloodUnit $bloodUnit): RedirectResponse
    {
        $this->authorizeBloodBankMenu();
        $this->ensureBranchBloodUnit($bloodUnit);
        $this->authorizeReceiveBloodUnits();

        $request->validate(['reason' => 'nullable|string|max:2000']);

        try {
            app(BloodUnitManagementService::class)->setQuarantine($bloodUnit, false, $request->input('reason'));
        } catch (\Throwable $e) {
            return redirect()
                ->route('react.blood-banks.inventory.show', $bloodUnit)
                ->with('error', $e->getMessage());
        }

        return redirect()
            ->route('react.blood-banks.inventory.show', $bloodUnit)
            ->with('success', localize('global.blood_unit_quarantine_released'));
    }

    protected function authorizeReceiveBloodUnits(): void
    {
        $user = request()->user();
        abort_unless(
            $user?->can('receive-blood-units') || $user?->can('manage-blood-inventory'),
            403,
        );
    }

    protected function ensureBranchBloodUnit(BloodUnit $unit): void
    {
        if ((int) $unit->branch_id !== (int) request()->user()?->branch_id) {
            abort(404);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function transformBloodUnitShow(BloodUnit $bloodUnit): array
    {
        $formatDt = fn ($dt) => $dt ? verta($dt)->format('Y-m-d H:i') : null;
        $expiresAt = $bloodUnit->expires_at;
        $isExpired = $expiresAt?->isPast() ?? false;
        $isExpiringSoon = $expiresAt
            && $expiresAt->isFuture()
            && $expiresAt->lte(now()->addDays(7))
            && in_array($bloodUnit->status, ['available', 'quarantine'], true);
        $daysUntilExpiry = $expiresAt && ! $isExpired
            ? (int) now()->diffInDays($expiresAt, absolute: false)
            : null;

        return [
            'id' => $bloodUnit->id,
            'bag_number' => $bloodUnit->bag_number,
            'blood_group' => $bloodUnit->blood_group,
            'rh' => $bloodUnit->rh,
            'component_type' => $bloodUnit->component_type,
            'status' => $bloodUnit->status,
            'volume_ml' => $bloodUnit->volume_ml,
            'collected_at' => $formatDt($bloodUnit->collected_at),
            'expires_at' => $formatDt($bloodUnit->expires_at),
            'is_expired' => $isExpired,
            'is_expiring_soon' => (bool) $isExpiringSoon,
            'days_until_expiry' => $daysUntilExpiry,
            'branch_name' => $bloodUnit->branch?->name,
            'screening_status' => $bloodUnit->test?->overall_status ?? 'pending',
            'test' => $bloodUnit->test ? $this->transformBloodUnitTest($bloodUnit->test) : null,
            'tests' => $bloodUnit->tests->map(fn ($test) => $this->transformBloodUnitTest($test))->values()->all(),
            'donation' => $bloodUnit->donation ? [
                'donor_name' => $bloodUnit->donation->donor?->name,
                'department_name' => $bloodUnit->donation->donor?->department?->name,
                'patient' => $bloodUnit->donation->donor?->patient ? [
                    'id' => $bloodUnit->donation->donor->patient->id,
                    'name' => trim($bloodUnit->donation->donor->patient->name.' '.($bloodUnit->donation->donor->patient->last_name ?? '')),
                    'urls' => [
                        'show' => route('react.patients.show', $bloodUnit->donation->donor->patient),
                    ],
                ] : null,
                'phlebotomy_at' => $formatDt($bloodUnit->donation->phlebotomy_at),
                'samples_count' => $bloodUnit->donation->samples?->count() ?? 0,
            ] : null,
            'stock_movements' => $bloodUnit->stockMovements
                ->sortByDesc('created_at')
                ->values()
                ->map(fn ($movement) => [
                    'id' => $movement->id,
                    'movement_type' => $movement->movement_type,
                    'user_name' => $movement->user?->name,
                    'notes' => $movement->notes,
                    'created_at' => $formatDt($movement->created_at),
                ])
                ->all(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function transformBloodUnitTest(BloodUnitTest $test): array
    {
        return [
            'id' => $test->id,
            'abo_result' => $test->abo_result,
            'rh_result' => $test->rh_result,
            'dct_result' => $test->dct_result,
            'ict_result' => $test->ict_result,
            'hbs_result' => $test->hbs_result,
            'hcv_result' => $test->hcv_result,
            'hiv_result' => $test->hiv_result,
            'vdrl_result' => $test->vdrl_result,
            'overall_status' => $test->overall_status,
            'remarks' => $test->remarks,
            'tested_at' => $test->tested_at ? verta($test->tested_at)->format('Y-m-d H:i') : null,
            'tested_by_name' => $test->testedBy?->name,
        ];
    }
}
