<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use Illuminate\Http\Request;

class BranchController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $branches = Branch::latest()->paginate(10);
        return view('pages.branches.index', compact('branches'));
    }

    public function create()
    {
        return view('pages.branches.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required',
            'address' => 'required',
        ]);

        $branch = Branch::create($data);

        return redirect()->route('branches.index')->with('success', localize('global.branch_created_successfully.'));
    }

    public function show(Branch $branch)
    {

    }

    public function edit(Branch $branch)
    {
        return view('pages.branches.edit', compact('branch'));
    }

    public function update(Request $request, Branch $branch)
    {
        $data = $request->validate([
            'name' => 'required',
            'address' => 'required',

        ]);

        $branch->update($data);

        return redirect()->route('branches.index')->with('success', localize('global.branch_updated_successfully.'));
    }

    public function destroy(Branch $branch)
    {
        $branch->delete();
        return redirect()->route('branches.index')->with('success', localize('global.branch_deleted_successfully.'));

    }
}
