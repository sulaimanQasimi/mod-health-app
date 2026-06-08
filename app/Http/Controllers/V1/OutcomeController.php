<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Controllers\V1\Concerns\AuthorizesPharmacyStockAccess;
use App\Http\Controllers\V1\Concerns\PaginatesInertiaIndex;
use App\Models\Pharmacy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class OutcomeController extends Controller
{
    use AuthorizesPharmacyStockAccess;
    use PaginatesInertiaIndex;

    private const FILTER_KEYS = ['search', 'pharmacy_id', 'date_from', 'date_to', 'sort_by', 'sort_order', 'per_page'];

    public function index(Request $request): Response
    {
        $this->authorizePharmacyManager($request->user());

        $user = $request->user();
        $query = $this->buildIndexQuery($request, $user);
        $perPage = (int) $request->get('per_page', 15);
        $paginator = $query->paginate($perPage > 0 ? $perPage : 15)->withQueryString();

        return Inertia::render('Outcomes/Index', [
            'outcomes' => [
                'data' => collect($paginator->items())->map(fn ($item) => [
                    'id' => $item->id,
                    'name' => $item->name,
                    'pharmacy_id' => $item->pharmacy_id,
                    'pharmacy_name' => $item->pharmacy_name,
                    'usage_count' => (int) $item->usage_count,
                    'updated_by_name' => $item->updated_by_name ? trim($item->updated_by_name) : null,
                    'prescription_updated_at' => $item->prescription_updated_at,
                ])->values()->all(),
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
            'filters' => $this->collectFilters($request, self::FILTER_KEYS),
            'filterOptions' => [
                'pharmacies' => ($user->activePharmacies->isEmpty() || $this->isPharmacyAdmin($user))
                    ? Pharmacy::query()->orderBy('name')->get(['id', 'name'])
                    : [],
            ],
            'urls' => [
                'index' => route('react.outcomes.index'),
                'report' => route('react.outcomes.report'),
                'export' => route('outcomes.export-index-report'),
            ],
        ]);
    }

    public function report(Request $request): Response
    {
        $this->authorizePharmacyManager($request->user());

        $user = $request->user();
        $query = $this->buildReportQuery($request, $user);
        $perPage = (int) $request->get('per_page', 15);
        $paginator = $query->paginate($perPage > 0 ? $perPage : 15)->withQueryString();

        return Inertia::render('Outcomes/Report', [
            'outcomes' => [
                'data' => collect($paginator->items())->map(fn ($item) => [
                    'id' => $item->id,
                    'name' => $item->name,
                    'usage_count' => (int) $item->usage_count,
                ])->values()->all(),
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
            'filters' => $this->collectFilters($request, self::FILTER_KEYS),
            'filterOptions' => [
                'pharmacies' => $this->isPharmacyAdmin($user)
                    ? Pharmacy::query()->orderBy('name')->get(['id', 'name'])
                    : [],
            ],
            'urls' => [
                'index' => route('react.outcomes.index'),
                'report' => route('react.outcomes.report'),
            ],
        ]);
    }

    private function buildIndexQuery(Request $request, \App\Models\User $user)
    {
        $userPharmacies = $user->activePharmacies;

        $usageSubquery = DB::table('prescription_items as pi')
            ->leftJoin('prescription_alternative_items as pai', function ($join) {
                $join->on('pai.prescription_item_id', '=', 'pi.id')
                    ->on('pai.prescription_id', '=', 'pi.prescription_id')
                    ->whereRaw('(pai.is_selected = 1 AND pai.deleted_at IS NULL)');
            })
            ->join('prescriptions as p', 'pi.prescription_id', '=', 'p.id')
            ->leftJoin('users as updater', 'p.updated_by', '=', 'updater.id')
            ->leftJoin('pharmacies as ph', 'p.pharmacy_id', '=', 'ph.id')
            ->whereNull('pi.deleted_at')
            ->whereNull('p.deleted_at')
            ->where('p.is_completed', 1)
            ->whereNotNull('p.pharmacy_id');

        if ($userPharmacies->isNotEmpty()) {
            $usageSubquery->whereIn('p.pharmacy_id', $userPharmacies->pluck('id'));
        }

        if ($request->filled('pharmacy_id') && ($this->isPharmacyAdmin($user) || $userPharmacies->isEmpty())) {
            $usageSubquery->where('p.pharmacy_id', $request->pharmacy_id);
        }

        if ($from = $this->parseOptionalDate($request->date_from)) {
            $usageSubquery->whereDate('p.created_at', '>=', $from);
        }

        if ($to = $this->parseOptionalDate($request->date_to)) {
            $usageSubquery->whereDate('p.created_at', '<=', $to);
        }

        $usageSubquery->select(
            DB::raw('COALESCE(pai.medicine_id, pi.medicine_id) as medicine_id'),
            'p.pharmacy_id',
            DB::raw('MAX(ph.name) as pharmacy_name'),
            DB::raw('COUNT(*) as usage_count'),
            DB::raw("SUBSTRING_INDEX(GROUP_CONCAT(TRIM(CONCAT(COALESCE(updater.name,''), ' ', COALESCE(updater.last_name,''))) ORDER BY p.updated_at DESC SEPARATOR '\t'), '\t', 1) as updated_by_name"),
            DB::raw('MAX(p.updated_at) as prescription_updated_at'),
        )->groupBy(DB::raw('COALESCE(pai.medicine_id, pi.medicine_id)'), 'p.pharmacy_id');

        $usageClone = clone $usageSubquery;
        $query = DB::table(DB::raw('('.$usageClone->toSql().') as u'))
            ->mergeBindings($usageClone)
            ->join('medicines as m', 'u.medicine_id', '=', 'm.id')
            ->whereNull('m.deleted_at')
            ->select(
                'm.id',
                'm.name',
                'u.pharmacy_id',
                'u.pharmacy_name',
                'u.usage_count',
                'u.updated_by_name',
                'u.prescription_updated_at',
            );

        if ($request->filled('search')) {
            $query->where('m.name', 'like', '%'.$request->search.'%');
        }

        $sortBy = $request->get('sort_by', 'usage_count');
        $sortOrder = $request->get('sort_order', 'desc') === 'asc' ? 'asc' : 'desc';
        $validSortColumns = [
            'id' => 'm.id',
            'name' => 'm.name',
            'pharmacy_name' => 'u.pharmacy_name',
            'usage_count' => 'u.usage_count',
            'updated_by_name' => 'u.updated_by_name',
            'prescription_updated_at' => 'u.prescription_updated_at',
        ];
        $sortColumn = $validSortColumns[$sortBy] ?? 'u.usage_count';
        $query->orderBy($sortColumn, $sortOrder);

        return $query;
    }

    private function buildReportQuery(Request $request, \App\Models\User $user)
    {
        $query = DB::table('prescription_items as pi')
            ->leftJoin('prescription_alternative_items as pai', function ($join) {
                $join->on('pai.prescription_item_id', '=', 'pi.id')
                    ->on('pai.prescription_id', '=', 'pi.prescription_id')
                    ->whereRaw('(pai.is_selected = 1 AND pai.deleted_at IS NULL)');
            })
            ->join('prescriptions as p', 'pi.prescription_id', '=', 'p.id')
            ->join('medicines as m', function ($join) {
                $join->whereRaw('m.id = COALESCE(pai.medicine_id, pi.medicine_id)');
            })
            ->whereNull('pi.deleted_at')
            ->whereNull('m.deleted_at')
            ->whereNull('p.deleted_at')
            ->select(
                'm.id',
                'm.name',
                DB::raw('COUNT(*) as usage_count'),
            )
            ->groupBy('m.id', 'm.name');

        $userPharmacies = $user->activePharmacies;

        if ($userPharmacies->isNotEmpty()) {
            $query->whereIn('p.pharmacy_id', $userPharmacies->pluck('id'));
        }

        if ($request->filled('search')) {
            $query->where('m.name', 'like', '%'.$request->search.'%');
        }

        if ($request->filled('pharmacy_id') && $this->isPharmacyAdmin($user)) {
            $query->where('p.pharmacy_id', $request->pharmacy_id);
        }

        if ($from = $this->parseOptionalDate($request->date_from)) {
            $query->whereDate('p.created_at', '>=', $from);
        }

        if ($to = $this->parseOptionalDate($request->date_to)) {
            $query->whereDate('p.created_at', '<=', $to);
        }

        $sortBy = $request->get('sort_by', 'usage_count');
        $sortOrder = $request->get('sort_order', 'desc') === 'asc' ? 'asc' : 'desc';
        $validSortColumns = ['id' => 'm.id', 'name' => 'm.name', 'usage_count' => 'usage_count'];
        $sortColumn = $validSortColumns[$sortBy] ?? 'usage_count';
        $query->orderBy($sortColumn, $sortOrder);

        return $query;
    }
}
