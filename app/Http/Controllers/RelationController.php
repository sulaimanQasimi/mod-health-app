<?php

namespace App\Http\Controllers;

use App\Models\Relation;
use Illuminate\Http\Request;

class RelationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $relations = Relation::all();
        return view('pages.relations.index',compact('relations'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('pages.relations.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required',
        ]);

        Relation::create($data);

        return redirect()->route('relations.index')->with('success', localize('global.relation_created_successfully.'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Relation $relation)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Relation $relation)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Relation $relation)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Relation $relation)
    {
        //
    }
}
