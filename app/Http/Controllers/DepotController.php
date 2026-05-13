<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Department;
use App\Models\Depot;
use App\Models\Pharmacy;
use Illuminate\Http\Request;
use App\Models\QueryBuilder\DepotQuery;

class DepotController extends Controller
{
    public function index(Request $request)
    {
        $query = Depot::query()->with(['department', 'branch', 'pharmacy', 'parentDepot']);
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
        $depots = $query->paginate(15);
        return view('pages.depots.index', compact('depots'));
    }
    public function create()
    {
        $branches = Branch::all();
        $departments = Department::all();
        $pharmacies = Pharmacy::all();
        $depots = Depot::all();
        return view('pages.depots.create', compact('branches', 'departments', 'pharmacies', 'depots'));
    }
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required',
            'address' => 'nullable|string|max:255',
            'department_id' => 'nullable|exists:departments,id',
            'branch_id' => 'nullable|exists:branches,id',
            'pharmacy_id' => 'nullable|exists:pharmacies,id',
            'parent_depot_id' => 'nullable|exists:depots,id',
        ]);
        Depot::create($data);
        return redirect()->route('depots.index')->with('success', localize('global.depot_created_successfully.'));
    }
    public function show(Depot $depot)
    {
        return view('pages.depots.show', compact('depot'));
    }
    public function edit(Depot $depot)
    {
        $branches = Branch::all();
        $departments = Department::all();
        $pharmacies = Pharmacy::all();
        $depots = Depot::all();
        return view('pages.depots.edit', compact('depot', 'branches', 'departments', 'pharmacies', 'depots'));
    }
    public function update(Request $request, Depot $depot)
    {
        $data = $request->validate([
            'name' => 'required',
            'address' => 'nullable|string|max:255',
            'department_id' => 'nullable|exists:departments,id',
            'branch_id' => 'nullable|exists:branches,id',
            'pharmacy_id' => 'nullable|exists:pharmacies,id',
            'parent_depot_id' => 'nullable|exists:depots,id',
        ]);
        $depot->update($data);
        return redirect()->route('depots.index')->with('success', localize('global.depot_updated_successfully.'));
    }
    public function destroy(Depot $depot)
    {
        $depot->delete();
        return redirect()->route('depots.index')->with('success', localize('global.depot_deleted_successfully.'));
    }
}   
