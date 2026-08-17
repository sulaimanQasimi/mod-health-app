<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Department;
use App\Models\Depot;
use App\Models\DepotRequest;
use App\Models\DepotTransaction;
use App\Models\Pharmacy;
use App\Models\User;
use App\Services\DepotStockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\QueryBuilder\DepotQuery;

class DepotController extends Controller
{
    public function __construct(
        private readonly DepotStockService $stockService
    ) {
    }

    public function index(Request $request)
    {
        $query = Depot::query()->with(['department', 'branch', 'pharmacy', 'parentDepot', 'activeUsers']);
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }
        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }
        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }
        if ($request->filled('pharmacy_id')) {
            $query->where('pharmacy_id', $request->pharmacy_id);
        }
        if ($request->filled('parent_depot_id')) {
            $query->where('parent_depot_id', $request->parent_depot_id);
        }
        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active);
        }
        if ($request->filled('is_base')) {
            $query->where('is_base', $request->is_base);
        }
        $depots = $query->orderBy('name')->paginate(15)->appends($request->query());

        return view('pages.depots.index', [
            'depots' => $depots,
            'branches' => Branch::query()->orderBy('name')->get(),
            'departments' => Department::query()->orderBy('name')->get(),
            'pharmacies' => Pharmacy::query()->orderBy('name')->get(),
            'parentDepots' => Depot::query()->orderBy('name')->get(),
        ]);
    }
    public function create()
    {
        $branches = Branch::all();
        $departments = Department::all();
        $pharmacies = Pharmacy::all();
        $depots = Depot::all();
        $users = User::orderBy('name')->get();
        return view('pages.depots.create', compact('branches', 'departments', 'pharmacies', 'depots', 'users'));
    }
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required',
            'address' => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean',
            'is_base' => 'nullable|boolean',
            'department_id' => 'nullable|exists:departments,id',
            'branch_id' => 'nullable|exists:branches,id',
            'pharmacy_id' => 'nullable|exists:pharmacies,id',
            'parent_depot_id' => 'nullable|exists:depots,id',
            'user_ids' => 'nullable|array',
            'user_ids.*' => 'nullable|exists:users,id',
            'roles' => 'nullable|array',
            'roles.*' => 'nullable|in:manager,staff,procurement,viewer',
        ]);

        $depot = DB::transaction(function () use ($data, $request) {
            $depot = Depot::create([
                ...collect($data)->except(['user_ids', 'roles'])->all(),
                'is_active' => $request->boolean('is_active', true),
                'is_base' => $request->boolean('is_base'),
                'created_by' => Auth::id(),
            ]);

            $depot->syncUsers($data['user_ids'] ?? [], $data['roles'] ?? []);

            return $depot;
        });

        if ($request->ajax()) {
            return response()->json([
                'message' => localize('global.depot_created_successfully.'),
                'redirect' => route('depots.show', $depot),
            ]);
        }

        return redirect()->route('depots.index')->with('success', localize('global.depot_created_successfully.'));
    }
    public function show(Depot $depot)
    {
        $depot->load(['department', 'branch', 'pharmacy', 'parentDepot', 'activeUsers']);

        $stockItems = $this->stockService->stockItemsForDepot($depot->id)->take(10);

        $recentTransactions = DepotTransaction::query()
            ->with(['medicine', 'tool', 'fromDepot', 'toDepot', 'pharmacy'])
            ->forDepot($depot->id)
            ->latest('transaction_date')
            ->latest('id')
            ->limit(10)
            ->get();

        $pendingOutgoingRequests = DepotRequest::query()
            ->with(['items.medicine', 'items.tool', 'sourceDepot'])
            ->where('requesting_depot_id', $depot->id)
            ->whereIn('status', [DepotRequest::STATUS_DRAFT, DepotRequest::STATUS_PENDING, DepotRequest::STATUS_APPROVED])
            ->latest('id')
            ->limit(5)
            ->get();

        $pendingIncomingRequests = DepotRequest::query()
            ->with(['items.medicine', 'items.tool', 'requestingDepot'])
            ->where('source_depot_id', $depot->id)
            ->whereIn('status', [DepotRequest::STATUS_PENDING, DepotRequest::STATUS_APPROVED])
            ->latest('id')
            ->limit(5)
            ->get();

        return view('pages.depots.show', compact(
            'depot',
            'stockItems',
            'recentTransactions',
            'pendingOutgoingRequests',
            'pendingIncomingRequests'
        ));
    }

    public function stock(Request $request, Depot $depot)
    {
        $itemType = $request->get('item_type');
        $search = $request->get('search');

        $stockItems = $this->stockService->stockItemsForDepot($depot->id, $itemType, $search);

        return view('pages.depots.stock', compact('depot', 'stockItems', 'itemType', 'search'));
    }
    public function edit(Depot $depot)
    {
        $depot->load('activeUsers');
        $branches = Branch::all();
        $departments = Department::all();
        $pharmacies = Pharmacy::all();
        $depots = Depot::all();
        $users = User::orderBy('name')->get();
        return view('pages.depots.edit', compact('depot', 'branches', 'departments', 'pharmacies', 'depots', 'users'));
    }
    public function update(Request $request, Depot $depot)
    {
        $data = $request->validate([
            'name' => 'required',
            'address' => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean',
            'is_base' => 'nullable|boolean',
            'department_id' => 'nullable|exists:departments,id',
            'branch_id' => 'nullable|exists:branches,id',
            'pharmacy_id' => 'nullable|exists:pharmacies,id',
            'parent_depot_id' => 'nullable|exists:depots,id|not_in:' . $depot->id,
            'user_ids' => 'nullable|array',
            'user_ids.*' => 'nullable|exists:users,id',
            'roles' => 'nullable|array',
            'roles.*' => 'nullable|in:manager,staff,procurement,viewer',
        ]);

        DB::transaction(function () use ($data, $request, $depot) {
            $depot->update([
                ...collect($data)->except(['user_ids', 'roles'])->all(),
                'is_active' => $request->boolean('is_active'),
                'is_base' => $request->boolean('is_base'),
                'updated_by' => Auth::id(),
            ]);

            $depot->syncUsers($data['user_ids'] ?? [], $data['roles'] ?? []);
        });

        if ($request->ajax()) {
            return response()->json([
                'message' => localize('global.depot_updated_successfully.'),
                'redirect' => route('depots.show', $depot),
            ]);
        }

        return redirect()->route('depots.index')->with('success', localize('global.depot_updated_successfully.'));
    }
    public function destroy(Depot $depot)
    {
        $depot->delete();
        return redirect()->route('depots.index')->with('success', localize('global.depot_deleted_successfully.'));
    }
}   
