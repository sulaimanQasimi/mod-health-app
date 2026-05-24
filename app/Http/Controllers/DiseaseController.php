<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Disease;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DiseaseController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $diseases = Disease::with('department')->paginate(5);
        return view('pages.diseases.index', compact('diseases'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $departments = Department::orderBy('name')->get();

        return view('pages.diseases.create', compact('departments'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => [
                'required',
                'max:255',
                Rule::unique('diseases')->where(fn ($query) => $query->where('department_id', $request->department_id)),
            ],
            'description' => 'nullable',
            'department_id' => 'required|exists:departments,id',
        ]);

        Disease::create($validatedData);

        return redirect()->route('diseases.index')->with('success', localize('global.disease_created_successfully.'));
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Disease  $disease
     * @return \Illuminate\Http\Response
     */
    public function show(Disease $disease)
    {
        $disease->load('department');

        return view('pages.diseases.show', compact('disease'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Disease  $disease
     * @return \Illuminate\Http\Response
     */
    public function edit(Disease $disease)
    {
        $departments = Department::orderBy('name')->get();

        return view('pages.diseases.edit', compact('disease', 'departments'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Disease  $disease
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Disease $disease)
    {
        $validatedData = $request->validate([
            'name' => [
                'required',
                'max:255',
                Rule::unique('diseases')->where(fn ($query) => $query->where('department_id', $request->department_id))->ignore($disease->id),
            ],
            'description' => 'nullable',
            'department_id' => 'required|exists:departments,id',
        ]);

        $disease->update($validatedData);

        return redirect()->route('diseases.index')->with('success', localize('global.disease_updated_successfully.'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Disease  $disease
     * @return \Illuminate\Http\Response
     */
    public function destroy(Disease $disease)
    {
        $disease->delete();

        return redirect()->route('diseases.index')->with('success', localize('global.disease_deleted_successfully.'));
    }
}
